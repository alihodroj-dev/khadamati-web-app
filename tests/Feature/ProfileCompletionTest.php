<?php

namespace Tests\Feature;

use App\Models\IdentityVerificationSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_complete_profile_after_social_login(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'email' => 'social@example.com',
            'password' => null,
            'role' => User::ROLE_CITIZEN,
            'first_name' => null,
            'last_name' => null,
        ]);

        $session = $this->createConsumableSession();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/profile/complete', [
            'verification_session_token' => $session->session_token,
            'first_name' => 'Ali',
            'last_name' => 'Hodroj',
            'father_name' => 'Salah',
            'mother_name' => 'Fatma Alyan',
            'date_of_birth' => '2004-11-27',
            'national_id' => '00073028821',
            'phone' => '+96170000001',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.profile_completed', true)
            ->assertJsonPath('data.user.first_name', 'Ali')
            ->assertJsonPath('data.user.national_id', '00073028821');

        $user->refresh();

        $this->assertNotNull($user->id_front_path);
        $this->assertNotNull($user->id_back_path);
    }

    protected function createConsumableSession(): IdentityVerificationSession
    {
        Storage::disk('public')->put('identity-sessions/test-session/front.jpg', 'front');
        Storage::disk('public')->put('identity-sessions/test-session/back.jpg', 'back');

        return IdentityVerificationSession::create([
            'session_token' => 'test-session-token',
            'id_front_path' => 'identity-sessions/test-session/front.jpg',
            'id_back_path' => 'identity-sessions/test-session/back.jpg',
            'extracted_data' => [],
            'ocr_raw_text' => null,
            'status' => IdentityVerificationSession::STATUS_PENDING,
            'expires_at' => now()->addDay(),
        ]);
    }
}
