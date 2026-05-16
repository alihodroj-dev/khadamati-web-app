<?php

namespace Tests\Feature;

use App\Models\IdentityVerificationSession;
use App\Services\OcrSpaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class IdentityPreviewTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_identity_preview_does_not_require_authentication(): void
    {
        Storage::fake('public');

        $this->mock(OcrSpaceService::class, function ($mock) {
            $mock->shouldReceive('extractText')
                ->once()
                ->andReturn("First Name: Ali\nLast Name: Hodroj\nNational ID: 1234567890");
        });

        $response = $this->postJson('/api/identity/preview', [
            'id_front' => UploadedFile::fake()->image('front.jpg'),
            'id_back' => UploadedFile::fake()->image('back.jpg'),
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Identity preview generated successfully.')
            ->assertJsonStructure([
                'data' => [
                    'verification_session_token',
                    'fields' => [
                        ['key', 'label', 'type', 'value', 'editable', 'required'],
                    ],
                ],
            ])
            ->assertJsonPath('data.fields.0.key', 'first_name')
            ->assertJsonPath('data.fields.0.value', 'Ali')
            ->assertJsonPath('data.fields.1.value', 'Hodroj')
            ->assertJsonPath('data.fields.5.value', '1234567890');

        $sessionToken = $response->json('data.verification_session_token');

        $this->assertDatabaseHas('identity_verification_sessions', [
            'session_token' => $sessionToken,
            'status' => IdentityVerificationSession::STATUS_PENDING,
        ]);

        Storage::disk('public')->assertExists('identity-sessions/'.$sessionToken.'/front.jpg');
        Storage::disk('public')->assertExists('identity-sessions/'.$sessionToken.'/back.jpg');
    }

    public function test_identity_preview_returns_empty_fields_when_ocr_fails(): void
    {
        Storage::fake('public');

        $this->mock(OcrSpaceService::class, function ($mock) {
            $mock->shouldReceive('extractText')
                ->once()
                ->andThrow(new \RuntimeException('OCR.space request failed.'));
        });

        $response = $this->postJson('/api/identity/preview', [
            'id_front' => UploadedFile::fake()->image('front.jpg'),
            'id_back' => UploadedFile::fake()->image('back.jpg'),
        ]);

        $response->assertOk()
            ->assertJsonPath('data.fields.0.value', '')
            ->assertJsonPath('data.fields.4.value', null);

        $this->assertDatabaseHas('identity_verification_sessions', [
            'session_token' => $response->json('data.verification_session_token'),
            'status' => IdentityVerificationSession::STATUS_FAILED,
            'ocr_raw_text' => null,
        ]);
    }

    public function test_identity_preview_validates_required_files(): void
    {
        $response = $this->postJson('/api/identity/preview', []);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed.');
    }

    public function test_verify_id_route_is_removed(): void
    {
        $response = $this->postJson('/api/verify-id', [], [
            'Authorization' => 'Bearer invalid-token',
        ]);

        $response->assertNotFound();
    }
}
