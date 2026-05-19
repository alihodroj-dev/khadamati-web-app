<?php

namespace Tests\Feature;

use App\Models\SocialAccount;
use App\Models\User;
use App\Services\GoogleIdTokenVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_google_auth_creates_citizen_user_and_social_account(): void
    {
        config(['services.google.web_client_id' => 'test-client-id.apps.googleusercontent.com']);

        $payload = [
            'sub' => 'google-user-123',
            'email' => 'citizen@example.com',
            'email_verified' => true,
            'name' => 'Citizen User',
            'picture' => 'https://example.com/avatar.jpg',
            'iss' => 'accounts.google.com',
            'aud' => 'test-client-id.apps.googleusercontent.com',
            'exp' => time() + 3600,
        ];

        $this->mock(GoogleIdTokenVerificationService::class, function ($mock) use ($payload) {
            $mock->shouldReceive('verify')
                ->once()
                ->with('valid-google-token')
                ->andReturn($payload);
        });

        $response = $this->postJson('/api/auth/social', [
            'provider' => 'google',
            'id_token' => 'valid-google-token',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Logged in successfully')
            ->assertJsonPath('errors', null)
            ->assertJsonPath('data.user.email', 'citizen@example.com')
            ->assertJsonPath('data.user.role', User::ROLE_CITIZEN)
            ->assertJsonPath('data.profile_completed', false)
            ->assertJsonPath('data.user.profile_completed', false)
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email', 'role', 'profile_completed'],
                    'token',
                    'profile_completed',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'citizen@example.com',
            'role' => User::ROLE_CITIZEN,
            'password' => null,
        ]);

        $this->assertDatabaseHas('social_accounts', [
            'provider' => SocialAccount::PROVIDER_GOOGLE,
            'provider_user_id' => 'google-user-123',
            'email' => 'citizen@example.com',
            'avatar_url' => 'https://example.com/avatar.jpg',
        ]);
    }

    public function test_google_auth_links_existing_user_by_email(): void
    {
        config(['services.google.web_client_id' => 'test-client-id.apps.googleusercontent.com']);

        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
            'name' => 'Existing User',
            'role' => User::ROLE_CITIZEN,
        ]);

        $payload = [
            'sub' => 'google-user-456',
            'email' => 'existing@example.com',
            'email_verified' => true,
            'name' => 'Google Name',
            'picture' => null,
            'iss' => 'accounts.google.com',
            'aud' => 'test-client-id.apps.googleusercontent.com',
            'exp' => time() + 3600,
        ];

        $this->mock(GoogleIdTokenVerificationService::class, function ($mock) use ($payload) {
            $mock->shouldReceive('verify')
                ->once()
                ->andReturn($payload);
        });

        $response = $this->postJson('/api/auth/social', [
            'provider' => 'google',
            'id_token' => 'valid-google-token',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.id', $existingUser->id)
            ->assertJsonPath('data.user.email', 'existing@example.com');

        $this->assertDatabaseCount('users', 1);

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $existingUser->id,
            'provider' => SocialAccount::PROVIDER_GOOGLE,
            'provider_user_id' => 'google-user-456',
            'email' => 'existing@example.com',
        ]);
    }

    public function test_google_auth_returns_existing_social_account_user(): void
    {
        config(['services.google.web_client_id' => 'test-client-id.apps.googleusercontent.com']);

        $user = User::factory()->create([
            'email' => 'linked@example.com',
            'role' => User::ROLE_CITIZEN,
        ]);

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => SocialAccount::PROVIDER_GOOGLE,
            'provider_user_id' => 'google-user-789',
            'email' => 'linked@example.com',
            'avatar_url' => null,
        ]);

        $payload = [
            'sub' => 'google-user-789',
            'email' => 'linked@example.com',
            'email_verified' => true,
            'name' => $user->name,
            'picture' => 'https://example.com/new-avatar.jpg',
            'iss' => 'accounts.google.com',
            'aud' => 'test-client-id.apps.googleusercontent.com',
            'exp' => time() + 3600,
        ];

        $this->mock(GoogleIdTokenVerificationService::class, function ($mock) use ($payload) {
            $mock->shouldReceive('verify')
                ->once()
                ->andReturn($payload);
        });

        $response = $this->postJson('/api/auth/social', [
            'provider' => 'google',
            'id_token' => 'valid-google-token',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.id', $user->id);

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider_user_id' => 'google-user-789',
            'avatar_url' => 'https://example.com/new-avatar.jpg',
        ]);
    }

    public function test_google_auth_rejects_invalid_token(): void
    {
        config(['services.google.web_client_id' => 'test-client-id.apps.googleusercontent.com']);

        $this->mock(GoogleIdTokenVerificationService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once()
                ->andThrow(new InvalidArgumentException('Invalid Google ID token.'));
        });

        $response = $this->postJson('/api/auth/social', [
            'provider' => 'google',
            'id_token' => 'invalid-token',
        ]);

        $response->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid Google ID token.')
            ->assertJsonPath('data', null);
    }

    public function test_google_auth_rejects_inactive_user(): void
    {
        config(['services.google.web_client_id' => 'test-client-id.apps.googleusercontent.com']);

        User::factory()->create([
            'email' => 'inactive@example.com',
            'is_active' => false,
        ]);

        $payload = [
            'sub' => 'google-user-inactive',
            'email' => 'inactive@example.com',
            'email_verified' => true,
            'name' => 'Inactive User',
            'iss' => 'accounts.google.com',
            'aud' => 'test-client-id.apps.googleusercontent.com',
            'exp' => time() + 3600,
        ];

        $this->mock(GoogleIdTokenVerificationService::class, function ($mock) use ($payload) {
            $mock->shouldReceive('verify')
                ->once()
                ->andReturn($payload);
        });

        $response = $this->postJson('/api/auth/social', [
            'provider' => 'google',
            'id_token' => 'valid-google-token',
        ]);

        $response->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Your account is inactive.');
    }

    public function test_google_auth_validates_id_token_presence(): void
    {
        $response = $this->postJson('/api/auth/social', []);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed.');
    }

    public function test_social_login_uses_first_and_last_name_for_google_when_provided(): void
    {
        config(['services.google.web_client_id' => 'test-client-id.apps.googleusercontent.com']);

        $payload = [
            'sub' => 'google-user-name',
            'email' => 'named@example.com',
            'email_verified' => true,
            'name' => 'Token Name',
            'iss' => 'accounts.google.com',
            'aud' => 'test-client-id.apps.googleusercontent.com',
            'exp' => time() + 3600,
        ];

        $this->mock(GoogleIdTokenVerificationService::class, function ($mock) use ($payload) {
            $mock->shouldReceive('verify')->once()->andReturn($payload);
        });

        $response = $this->postJson('/api/auth/social', [
            'provider' => 'google',
            'id_token' => 'valid-google-token',
            'first_name' => 'Jane',
            'last_name' => 'Citizen',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.name', 'Jane Citizen');
    }
}
