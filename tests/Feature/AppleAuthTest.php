<?php

namespace Tests\Feature;

use App\Models\SocialAccount;
use App\Models\User;
use App\Services\AppleAuthenticationService;
use App\Services\AppleIdentityTokenVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Mockery;
use Tests\TestCase;

class AppleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_apple_auth_creates_citizen_user_with_full_name(): void
    {
        config(['services.apple.client_id' => 'com.khadamati.app']);

        $payload = [
            'sub' => 'apple-user-123',
            'email' => 'Citizen@Example.com',
            'iss' => 'https://appleid.apple.com',
            'aud' => 'com.khadamati.app',
            'exp' => time() + 3600,
        ];

        $this->mock(AppleIdentityTokenVerificationService::class, function ($mock) use ($payload) {
            $mock->shouldReceive('verify')
                ->once()
                ->with('valid-apple-token')
                ->andReturn($payload);
        });

        $response = $this->postJson('/api/auth/apple', [
            'identity_token' => 'valid-apple-token',
            'full_name' => 'Citizen User',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'citizen@example.com')
            ->assertJsonPath('data.user.name', 'Citizen User')
            ->assertJsonPath('data.user.role', User::ROLE_CITIZEN);

        $this->assertDatabaseHas('users', [
            'email' => 'citizen@example.com',
            'name' => 'Citizen User',
            'role' => User::ROLE_CITIZEN,
        ]);

        $this->assertDatabaseHas('social_accounts', [
            'provider' => SocialAccount::PROVIDER_APPLE,
            'provider_user_id' => 'apple-user-123',
            'email' => 'citizen@example.com',
        ]);
    }

    public function test_apple_auth_supports_private_relay_email(): void
    {
        config(['services.apple.client_id' => 'com.khadamati.app']);

        $relayEmail = 'abc123@privaterelay.appleid.com';

        $payload = [
            'sub' => 'apple-user-relay',
            'email' => $relayEmail,
            'is_private_email' => true,
            'iss' => 'https://appleid.apple.com',
            'aud' => 'com.khadamati.app',
            'exp' => time() + 3600,
        ];

        $this->mock(AppleIdentityTokenVerificationService::class, function ($mock) use ($payload) {
            $mock->shouldReceive('verify')->once()->andReturn($payload);
        });

        $response = $this->postJson('/api/auth/apple', [
            'identity_token' => 'valid-apple-token',
            'full_name' => 'Relay User',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.email', $relayEmail);

        $this->assertTrue(AppleAuthenticationService::isPrivateRelayEmail($relayEmail));
    }

    public function test_apple_auth_links_existing_user_by_email(): void
    {
        config(['services.apple.client_id' => 'com.khadamati.app']);

        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
            'name' => 'Existing User',
        ]);

        $payload = [
            'sub' => 'apple-user-456',
            'email' => 'existing@example.com',
            'iss' => 'https://appleid.apple.com',
            'aud' => 'com.khadamati.app',
            'exp' => time() + 3600,
        ];

        $this->mock(AppleIdentityTokenVerificationService::class, function ($mock) use ($payload) {
            $mock->shouldReceive('verify')->once()->andReturn($payload);
        });

        $response = $this->postJson('/api/auth/apple', [
            'identity_token' => 'valid-apple-token',
            'full_name' => 'Should Not Override',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.id', $existingUser->id)
            ->assertJsonPath('data.user.name', 'Existing User');

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $existingUser->id,
            'provider' => SocialAccount::PROVIDER_APPLE,
            'provider_user_id' => 'apple-user-456',
        ]);
    }

    public function test_apple_auth_returns_user_without_email_in_token_when_social_account_exists(): void
    {
        config(['services.apple.client_id' => 'com.khadamati.app']);

        $user = User::factory()->create([
            'email' => 'stored@privaterelay.appleid.com',
            'name' => 'Stored Name',
        ]);

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => SocialAccount::PROVIDER_APPLE,
            'provider_user_id' => 'apple-user-789',
            'email' => 'stored@privaterelay.appleid.com',
            'avatar_url' => null,
        ]);

        $payload = [
            'sub' => 'apple-user-789',
            'iss' => 'https://appleid.apple.com',
            'aud' => 'com.khadamati.app',
            'exp' => time() + 3600,
        ];

        $this->mock(AppleIdentityTokenVerificationService::class, function ($mock) use ($payload) {
            $mock->shouldReceive('verify')->once()->andReturn($payload);
        });

        $response = $this->postJson('/api/auth/apple', [
            'identity_token' => 'valid-apple-token',
            'full_name' => 'Ignored Name',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.name', 'Stored Name');
    }

    public function test_apple_auth_uses_email_local_part_when_full_name_missing_on_create(): void
    {
        config(['services.apple.client_id' => 'com.khadamati.app']);

        $payload = [
            'sub' => 'apple-user-noname',
            'email' => 'jane.doe@example.com',
            'iss' => 'https://appleid.apple.com',
            'aud' => 'com.khadamati.app',
            'exp' => time() + 3600,
        ];

        $this->mock(AppleIdentityTokenVerificationService::class, function ($mock) use ($payload) {
            $mock->shouldReceive('verify')->once()->andReturn($payload);
        });

        $response = $this->postJson('/api/auth/apple', [
            'identity_token' => 'valid-apple-token',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.name', 'jane.doe');
    }

    public function test_apple_auth_rejects_first_sign_in_without_email(): void
    {
        config(['services.apple.client_id' => 'com.khadamati.app']);

        $payload = [
            'sub' => 'apple-user-no-email',
            'iss' => 'https://appleid.apple.com',
            'aud' => 'com.khadamati.app',
            'exp' => time() + 3600,
        ];

        $this->mock(AppleIdentityTokenVerificationService::class, function ($mock) use ($payload) {
            $mock->shouldReceive('verify')->once()->andReturn($payload);
        });

        $response = $this->postJson('/api/auth/apple', [
            'identity_token' => 'valid-apple-token',
        ]);

        $response->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid Apple identity token.');
    }

    public function test_apple_auth_rejects_invalid_token(): void
    {
        config(['services.apple.client_id' => 'com.khadamati.app']);

        $this->mock(AppleIdentityTokenVerificationService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once()
                ->andThrow(new InvalidArgumentException('Invalid Apple identity token.'));
        });

        $response = $this->postJson('/api/auth/apple', [
            'identity_token' => 'invalid-token',
        ]);

        $response->assertUnauthorized()
            ->assertJsonPath('message', 'Invalid Apple identity token.');
    }

    public function test_apple_auth_rejects_inactive_user(): void
    {
        config(['services.apple.client_id' => 'com.khadamati.app']);

        User::factory()->create([
            'email' => 'inactive@example.com',
            'is_active' => false,
        ]);

        $payload = [
            'sub' => 'apple-user-inactive',
            'email' => 'inactive@example.com',
            'iss' => 'https://appleid.apple.com',
            'aud' => 'com.khadamati.app',
            'exp' => time() + 3600,
        ];

        $this->mock(AppleIdentityTokenVerificationService::class, function ($mock) use ($payload) {
            $mock->shouldReceive('verify')->once()->andReturn($payload);
        });

        $response = $this->postJson('/api/auth/apple', [
            'identity_token' => 'valid-apple-token',
        ]);

        $response->assertForbidden()
            ->assertJsonPath('message', 'Your account is inactive.');
    }

    public function test_apple_auth_validates_identity_token_presence(): void
    {
        $response = $this->postJson('/api/auth/apple', []);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Validation failed.');
    }
}
