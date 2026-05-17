<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IosApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_categories_endpoint_is_public(): void
    {
        $this->getJson('/api/service-categories')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['categories'],
            ]);
    }

    public function test_services_endpoint_is_public(): void
    {
        $this->getJson('/api/services')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['services'],
            ]);
    }

    public function test_offices_endpoint_is_public(): void
    {
        $this->getJson('/api/offices')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['offices'],
            ]);
    }

    public function test_me_returns_unauthorized_without_token(): void
    {
        $this->getJson('/api/me')
            ->assertUnauthorized();
    }

    public function test_me_returns_authenticated_user_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'ios-user@example.com',
        ]);

        $token = $user->createToken('ios-test')->plainTextToken;

        $this->getJson('/api/me', [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'ios-user@example.com');
    }
}
