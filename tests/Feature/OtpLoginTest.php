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

    public function test_login_returns_otp_challenge_instead_of_token(): void
    {
        Log::spy();

        $user = User::factory()->create([
            'email' => 'citizen@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'citizen@example.com',
            'password' => 'password123',
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

        $response = $this->postJson('/api/login/verify-otp', [
            'challenge_token' => $challenge->challenge_token,
            'otp' => $otp,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.email', 'citizen@example.com')
            ->assertJsonStructure(['data' => ['user', 'token']]);

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
        $this->assertTrue($challenge->expires_at->greaterThan(now()->addMinutes(5)));

        Log::shouldHaveReceived('info')->with('Login OTP generated.', \Mockery::type('array'));
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'citizen@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'citizen@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable();
        $this->assertDatabaseCount('otp_challenges', 0);
    }
}
