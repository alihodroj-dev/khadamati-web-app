<?php

namespace Tests\Feature;

use App\Models\IdentityVerificationSession;
use App\Services\OcrSpaceIdentityService;
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

        $this->mock(OcrSpaceIdentityService::class, function ($mock) {
            $mock->shouldReceive('extractFromFrontIdPath')
                ->once()
                ->andReturn([
                    'success' => true,
                    'raw_text' => "First Name: Ali\nLast Name: Hodroj\nNational ID: 1234567890",
                    'extracted_fields' => [
                        'first_name' => 'Ali',
                        'last_name' => 'Hodroj',
                        'father_name' => '',
                        'mother_name' => '',
                        'date_of_birth' => null,
                        'national_id' => '1234567890',
                    ],
                    'error' => null,
                ]);
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
                    'ocr' => ['success', 'error'],
                    'fields' => [
                        ['key', 'label', 'type', 'value', 'editable', 'required'],
                    ],
                ],
            ])
            ->assertJsonPath('data.ocr.success', true)
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

        $this->mock(OcrSpaceIdentityService::class, function ($mock) {
            $mock->shouldReceive('extractFromFrontIdPath')
                ->once()
                ->andReturn([
                    'success' => false,
                    'raw_text' => '',
                    'extracted_fields' => [
                        'first_name' => '',
                        'last_name' => '',
                        'father_name' => '',
                        'mother_name' => '',
                        'date_of_birth' => null,
                        'national_id' => '',
                    ],
                    'error' => OcrSpaceIdentityService::ERROR_PARSE,
                ]);
        });

        $response = $this->postJson('/api/identity/preview', [
            'id_front' => UploadedFile::fake()->image('front.jpg'),
            'id_back' => UploadedFile::fake()->image('back.jpg'),
        ]);

        $response->assertOk()
            ->assertJsonPath('data.ocr.success', false)
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

    public function test_identity_preview_accepts_base64_images(): void
    {
        Storage::fake('public');

        $this->mock(OcrSpaceIdentityService::class, function ($mock) {
            $mock->shouldReceive('extractFromFrontIdPath')
                ->once()
                ->andReturn([
                    'success' => true,
                    'raw_text' => 'الاسم: علي',
                    'extracted_fields' => [
                        'first_name' => 'Ali',
                        'last_name' => '',
                        'father_name' => '',
                        'mother_name' => '',
                        'date_of_birth' => null,
                        'national_id' => '',
                    ],
                    'error' => null,
                ]);
        });

        $front = base64_encode(UploadedFile::fake()->image('front.jpg')->getContent());
        $back = base64_encode(UploadedFile::fake()->image('back.jpg')->getContent());

        $response = $this->postJson('/api/identity/preview', [
            'id_front_base64' => $front,
            'id_back_base64' => $back,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.fields.0.value', 'Ali');
    }

    public function test_identity_preview_accepts_multipart_post(): void
    {
        Storage::fake('public');

        $this->mock(OcrSpaceIdentityService::class, function ($mock) {
            $mock->shouldReceive('extractFromFrontIdPath')
                ->once()
                ->andReturn([
                    'success' => false,
                    'raw_text' => '',
                    'extracted_fields' => [
                        'first_name' => '',
                        'last_name' => '',
                        'father_name' => '',
                        'mother_name' => '',
                        'date_of_birth' => null,
                        'national_id' => '',
                    ],
                    'error' => OcrSpaceIdentityService::ERROR_EMPTY,
                ]);
        });

        $response = $this->post('/api/identity/preview', [
            'id_front' => UploadedFile::fake()->image('front.jpg'),
            'id_back' => UploadedFile::fake()->image('back.jpg'),
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertOk();
    }

    public function test_verify_id_route_is_removed(): void
    {
        $response = $this->postJson('/api/verify-id', [], [
            'Authorization' => 'Bearer invalid-token',
        ]);

        $response->assertNotFound();
    }
}
