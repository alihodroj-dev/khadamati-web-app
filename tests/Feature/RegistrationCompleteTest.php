<?php

namespace Tests\Feature;

use App\Models\IdentityVerificationSession;
use App\Models\SocialAccount;
use App\Models\User;
use App\Services\GoogleIdTokenVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistrationCompleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_registration_complete_creates_citizen_and_consumes_session(): void
    {
        Storage::fake('public');

        $session = $this->createConsumableSession();

        $response = $this->postJson('/api/register/complete', [
            'verification_session_token' => $session->session_token,
            'auth_provider' => 'email',
            'email' => 'ali@example.com',
            'first_name' => 'Ali',
            'last_name' => 'Hodroj',
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'date_of_birth' => '2001-05-10',
            'national_id' => '123456789',
            'phone' => '+96170000001',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'ali@example.com')
            ->assertJsonPath('data.user.first_name', 'Ali')
            ->assertJsonPath('data.user.name', 'Ali Hodroj')
            ->assertJsonPath('data.user.national_id', '123456789')
            ->assertJsonStructure(['data' => ['user', 'token']]);

        $user = User::query()->where('email', 'ali@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('Ali Hodroj', $user->name);
        $this->assertNotNull($user->id_front_path);
        $this->assertNotNull($user->id_back_path);

        Storage::disk('public')->assertExists($user->id_front_path);
        Storage::disk('public')->assertExists($user->id_back_path);

        $this->assertDatabaseHas('identity_verification_sessions', [
            'id' => $session->id,
            'status' => IdentityVerificationSession::STATUS_CONSUMED,
        ]);
    }

    public function test_google_registration_complete_links_social_account(): void
    {
        Storage::fake('public');
        config(['services.google.client_id' => 'test-client-id.apps.googleusercontent.com']);

        $session = $this->createConsumableSession();

        $this->mock(GoogleIdTokenVerificationService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once()
                ->andReturn([
                    'sub' => 'google-user-999',
                    'email' => 'google@example.com',
                    'email_verified' => true,
                    'iss' => 'accounts.google.com',
                    'aud' => 'test-client-id.apps.googleusercontent.com',
                    'exp' => time() + 3600,
                ]);
        });

        $response = $this->postJson('/api/register/complete', [
            'verification_session_token' => $session->session_token,
            'auth_provider' => 'google',
            'provider_token' => 'valid-google-token',
            'email' => 'google@example.com',
            'first_name' => 'Ali',
            'last_name' => 'Hodroj',
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'date_of_birth' => '2001-05-10',
            'national_id' => '987654321',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.user.email', 'google@example.com');

        $user = User::query()->where('email', 'google@example.com')->first();

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => SocialAccount::PROVIDER_GOOGLE,
            'provider_user_id' => 'google-user-999',
        ]);

        $this->assertNull($user->password);
    }

    public function test_registration_complete_rejects_consumed_session(): void
    {
        Storage::fake('public');

        $session = $this->createConsumableSession([
            'status' => IdentityVerificationSession::STATUS_CONSUMED,
        ]);

        $response = $this->postJson('/api/register/complete', [
            'verification_session_token' => $session->session_token,
            'auth_provider' => 'email',
            'email' => 'ali@example.com',
            'first_name' => 'Ali',
            'last_name' => 'Hodroj',
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'date_of_birth' => '2001-05-10',
            'national_id' => '123456789',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_registration_complete_requires_password_confirmation_for_email(): void
    {
        Storage::fake('public');

        $session = $this->createConsumableSession();

        $response = $this->postJson('/api/register/complete', [
            'verification_session_token' => $session->session_token,
            'auth_provider' => 'email',
            'email' => 'ali@example.com',
            'first_name' => 'Ali',
            'last_name' => 'Hodroj',
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'date_of_birth' => '2001-05-10',
            'national_id' => '123456789',
            'password' => 'password123',
        ]);

        $response->assertUnprocessable();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function createConsumableSession(array $overrides = []): IdentityVerificationSession
    {
        $token = 'test-session-token-'.uniqid();
        $frontPath = "identity-sessions/{$token}/front.jpg";
        $backPath = "identity-sessions/{$token}/back.jpg";

        Storage::disk('public')->put($frontPath, 'front-image');
        Storage::disk('public')->put($backPath, 'back-image');

        return IdentityVerificationSession::create(array_merge([
            'session_token' => $token,
            'id_front_path' => $frontPath,
            'id_back_path' => $backPath,
            'ocr_raw_text' => null,
            'extracted_data' => null,
            'status' => IdentityVerificationSession::STATUS_PENDING,
            'expires_at' => now()->addDay(),
        ], $overrides));
    }
}
