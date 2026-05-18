<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FcmTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_save_fcm_token(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/fcm-token', [
                'fcm_token' => 'fcm-token-example',
            ])
            ->assertOk()
            ->assertJsonPath('data.has_fcm_token', true);

        $this->assertSame('fcm-token-example', $user->fresh()->fcm_token);
    }

    public function test_authenticated_user_can_update_fcm_token(): void
    {
        $user = User::factory()->create([
            'fcm_token' => 'old-token',
        ]);

        $this->actingAs($user)
            ->patchJson('/api/fcm-token', [
                'fcm_token' => 'new-token',
            ])
            ->assertOk()
            ->assertJsonPath('data.has_fcm_token', true);

        $this->assertSame('new-token', $user->fresh()->fcm_token);
    }

    public function test_authenticated_user_can_remove_fcm_token(): void
    {
        $user = User::factory()->create([
            'fcm_token' => 'token-to-remove',
        ]);

        $this->actingAs($user)
            ->deleteJson('/api/fcm-token')
            ->assertOk()
            ->assertJsonPath('data.has_fcm_token', false);

        $this->assertNull($user->fresh()->fcm_token);
    }

    public function test_fcm_token_is_required(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/fcm-token', [])
            ->assertJsonValidationErrors('fcm_token');
    }
}
