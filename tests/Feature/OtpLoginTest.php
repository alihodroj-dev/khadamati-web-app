<?php

namespace Tests\Feature;

use App\Models\OtpChallenge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class OtpLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_otp_returns_challenge_without_password(): void
    {
        Log::spy();

        $user = User::factory()->create([
            'email' => 'citizen@example.com',
            'password' => null,
            'role' => User::ROLE_CITIZEN,
        ]);

        $response = $this->postJson('/api/auth/request-otp', [
            'email' => 'citizen@example.com',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.requires_otp', true)
            ->assertJsonStructure([
                'data' => ['challenge_token', 'expires_at'],
            ])
            ->assertJsonMissingPath('data.token');

        $this->assertDatabaseHas('otp_challenges', [
            'user_id' => $user->id,
            'channel' => OtpChallenge::CHANNEL_EMAIL,
        ]);

        Log::shouldHaveReceived('info')
            ->once()
            ->with('Login OTP generated.', \Mockery::on(function (array $context) use ($user) {
                return $context['user_id'] === $user->id
                    && preg_match('/^\d{6}$/', $context['otp']);
            }));
    }

    public function test_verify_otp_returns_user_and_token(): void
    {
        $user = User::factory()->create([
            'email' => 'citizen@example.com',
            'password' => Hash::make('password123'),
        ]);

        $otp = '123456';

        $challenge = OtpChallenge::create([
            'challenge_token' => 'test-challenge-token',
            'user_id' => $user->id,
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
            'channel' => OtpChallenge::CHANNEL_EMAIL,
        ]);

        $response = $this->postJson('/api/auth/verify-otp', [
            'challenge_token' => $challenge->challenge_token,
            'otp' => $otp,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.email', 'citizen@example.com')
            ->assertJsonPath('data.user.profile_completed', false)
            ->assertJsonPath('data.profile_completed', false)
            ->assertJsonStructure(['data' => ['user', 'token', 'profile_completed']]);

        $challenge->refresh();

        $this->assertNotNull($challenge->consumed_at);
    }

    public function test_verify_otp_rejects_invalid_code_and_increments_attempts(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $challenge = OtpChallenge::create([
            'challenge_token' => 'test-challenge-token',
            'user_id' => $user->id,
            'otp_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(10),
            'channel' => OtpChallenge::CHANNEL_EMAIL,
        ]);

        $response = $this->postJson('/api/login/verify-otp', [
            'challenge_token' => $challenge->challenge_token,
            'otp' => '000000',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.otp.0', 'The verification code is invalid.');

        $this->assertSame(1, $challenge->fresh()->attempts);
    }

    public function test_resend_otp_refreshes_challenge(): void
    {
        Log::spy();

        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $challenge = OtpChallenge::create([
            'challenge_token' => 'test-challenge-token',
            'user_id' => $user->id,
            'otp_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinute(),
            'attempts' => 2,
            'channel' => OtpChallenge::CHANNEL_EMAIL,
        ]);

        $response = $this->postJson('/api/login/resend-otp', [
            'challenge_token' => $challenge->challenge_token,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.challenge_token', $challenge->challenge_token);

        $challenge->refresh();

        $this->assertSame(0, $challenge->attempts);
        $this->assertTrue($challenge->expires_at->greaterThan(now()->addMinutes(4)));

        Log::shouldHaveReceived('info')->with('Login OTP generated.', \Mockery::type('array'));
    }

    public function test_request_otp_creates_empty_citizen_for_new_email(): void
    {
        Log::spy();

        $response = $this->postJson('/api/auth/request-otp', [
            'email' => 'newuser@example.com',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.requires_otp', true);

        $user = User::query()->where('email', 'newuser@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame(User::ROLE_CITIZEN, $user->role);
        $this->assertNull($user->password);
        $this->assertNull($user->first_name);
        $this->assertNull($user->national_id);
        $this->assertDatabaseHas('otp_challenges', [
            'user_id' => $user->id,
        ]);
    }

    public function test_request_otp_rejects_inactive_user(): void
    {
        User::factory()->create([
            'email' => 'inactive@example.com',
            'role' => User::ROLE_CITIZEN,
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/auth/request-otp', [
            'email' => 'inactive@example.com',
        ]);

        $response->assertForbidden()
            ->assertJsonPath('message', 'Your account is inactive.');
    }

    public function test_verify_otp_rejects_deactivated_user(): void
    {
        $user = User::factory()->create([
            'is_active' => false,
        ]);

        $challenge = OtpChallenge::create([
            'challenge_token' => 'inactive-user-token',
            'user_id' => $user->id,
            'otp_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(10),
            'channel' => OtpChallenge::CHANNEL_EMAIL,
        ]);

        $response = $this->postJson('/api/auth/verify-otp', [
            'challenge_token' => $challenge->challenge_token,
            'otp' => '123456',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.challenge_token.0', 'This login challenge is no longer valid.');
    }

    public function test_new_user_can_verify_otp_and_sign_in_with_incomplete_profile(): void
    {
        Log::spy();

        $this->postJson('/api/auth/request-otp', [
            'email' => 'brandnew@example.com',
        ])->assertOk();

        $user = User::query()->where('email', 'brandnew@example.com')->firstOrFail();

        $otp = '111222';

        $challenge = OtpChallenge::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->firstOrFail();

        $challenge->update(['otp_hash' => Hash::make($otp)]);

        $response = $this->postJson('/api/auth/verify-otp', [
            'challenge_token' => $challenge->challenge_token,
            'otp' => $otp,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.profile_completed', false)
            ->assertJsonPath('data.user.profile_completed', false)
            ->assertJsonPath('data.user.email', 'brandnew@example.com');

        $this->assertFalse($user->fresh()->profile_completed);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_verify_otp_returns_profile_completed_true_for_complete_profile(): void
    {
        $user = User::factory()->create([
            'email' => 'complete@example.com',
            'password' => null,
            'first_name' => 'Ali',
            'last_name' => 'Hodroj',
            'father_name' => 'Salah',
            'mother_name' => 'Fatima',
            'date_of_birth' => '2004-11-27',
            'national_id' => '00073028821',
            'id_front_path' => 'id-documents/1/front.jpg',
            'id_back_path' => 'id-documents/1/back.jpg',
        ]);

        $otp = '654321';

        $challenge = OtpChallenge::create([
            'challenge_token' => 'complete-profile-token',
            'user_id' => $user->id,
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
            'channel' => OtpChallenge::CHANNEL_EMAIL,
        ]);

        $response = $this->postJson('/api/auth/verify-otp', [
            'challenge_token' => $challenge->challenge_token,
            'otp' => $otp,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.profile_completed', true)
            ->assertJsonPath('data.user.profile_completed', true);

        $this->assertTrue($user->fresh()->profile_completed);
    }
}
