<?php

namespace Tests\Feature;

use App\Models\User;
use App\Mail\LoginOtpMail;
use App\Models\OtpChallenge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WebLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_user_cannot_login_to_web_panel(): void
    {
        User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'is_active' => false,
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'inactive@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_staff_login_sends_otp_email_and_redirects_to_verification_page(): void
    {
        Mail::fake();

        $staff = User::factory()->create([
            'email' => 'staff@example.com',
            'role' => User::ROLE_STAFF,
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => 'staff@example.com',
        ])->assertRedirect(route('login.otp.show'));

        $this->assertDatabaseHas('otp_challenges', [
            'user_id' => $staff->id,
            'channel' => OtpChallenge::CHANNEL_EMAIL,
        ]);

        Mail::assertSent(LoginOtpMail::class, function (LoginOtpMail $mail) use ($staff) {
            return $mail->hasTo($staff->email)
                && preg_match('/^\d{6}$/', $mail->otp)
                && $mail->expiresInMinutes === 5;
        });
    }

    public function test_staff_can_verify_otp_and_login(): void
    {
        Mail::fake();

        $staff = User::factory()->create([
            'email' => 'staff@example.com',
            'role' => User::ROLE_STAFF,
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => 'staff@example.com',
        ])->assertRedirect(route('login.otp.show'));

        $otp = '123456';
        $challenge = OtpChallenge::query()->where('user_id', $staff->id)->firstOrFail();
        $challenge->update(['otp_hash' => Hash::make($otp)]);

        $this->post(route('login.otp.verify'), [
            'otp' => $otp,
        ])->assertRedirect(route('staff.dashboard'));

        $this->assertAuthenticatedAs($staff);
        $this->assertNotNull($challenge->fresh()->consumed_at);
    }

    public function test_admin_still_uses_password_login_without_otp(): void
    {
        Mail::fake();

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->assertDatabaseCount('otp_challenges', 0);
        Mail::assertNothingSent();
    }

    public function test_admin_cannot_start_otp_login_without_password(): void
    {
        Mail::fake();

        User::factory()->create([
            'email' => 'admin@example.com',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->from('/login')->post('/login', [
            'email' => 'admin@example.com',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors('password');

        $this->assertGuest();
        $this->assertDatabaseCount('otp_challenges', 0);
        Mail::assertNothingSent();
    }

    public function test_expired_web_otp_is_rejected(): void
    {
        Mail::fake();

        $staff = User::factory()->create([
            'email' => 'staff@example.com',
            'role' => User::ROLE_STAFF,
            'is_active' => true,
        ]);

        $challenge = OtpChallenge::create([
            'challenge_token' => 'expired-web-token',
            'user_id' => $staff->id,
            'otp_hash' => Hash::make('123456'),
            'expires_at' => now()->subMinute(),
            'channel' => OtpChallenge::CHANNEL_EMAIL,
        ]);

        $this->withSession([
            'otp_login' => [
                'challenge_token' => $challenge->challenge_token,
                'email' => $staff->email,
                'remember' => false,
            ],
        ])->post(route('login.otp.verify'), [
            'otp' => '123456',
        ])->assertSessionHasErrors('otp');

        $this->assertGuest();
    }
}
