<?php

namespace Tests\Unit;

use App\Services\OcrSpaceIdentityService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OcrSpaceIdentityServiceTest extends TestCase
{
    public function test_extract_from_front_id_path_returns_raw_text_and_fields(): void
    {
        Storage::fake('public');
        config([
            'services.ocr_space.api_key' => 'test-api-key',
            'services.ocr_space.endpoint' => 'https://api.ocr.space/parse/image',
        ]);

        $path = 'identity-sessions/test-session/front.jpg';
        $uploadedFile = UploadedFile::fake()->image('front.jpg');
        Storage::disk('public')->put($path, $uploadedFile->getContent());

        Http::fake([
            'api.ocr.space/*' => Http::response([
                'OCRExitCode' => 1,
                'ParsedResults' => [
                    ['ParsedText' => "First Name: Ali\nLast Name: Hodroj\nNational ID: 1234567890"],
                ],
            ]),
        ]);

        $service = new OcrSpaceIdentityService;
        $result = $service->extractFromFrontIdPath($path);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Ali', $result['raw_text']);
        $this->assertSame('Ali', $result['extracted_fields']['first_name']);
        $this->assertSame('Hodroj', $result['extracted_fields']['last_name']);
        $this->assertSame('1234567890', $result['extracted_fields']['national_id']);
        $this->assertNull($result['error']);

        Http::assertSent(function ($request) {
            return $request->hasHeader('apikey', 'test-api-key');
        });
    }

    public function test_extract_returns_clean_error_when_api_key_missing(): void
    {
        Storage::fake('public');
        config(['services.ocr_space.api_key' => null]);

        $path = 'identity-sessions/test-session/front.jpg';
        Storage::disk('public')->put($path, 'fake-image');

        $service = new OcrSpaceIdentityService;
        $result = $service->extractFromFrontIdPath($path);

        $this->assertFalse($result['success']);
        $this->assertSame('', $result['raw_text']);
        $this->assertSame(OcrSpaceIdentityService::ERROR_CONFIGURATION, $result['error']);
        $this->assertSame('', $result['extracted_fields']['first_name']);
    }

    public function test_extract_returns_clean_error_for_ocr_api_failure(): void
    {
        Storage::fake('public');
        config([
            'services.ocr_space.api_key' => 'test-api-key',
            'services.ocr_space.endpoint' => 'https://api.ocr.space/parse/image',
        ]);

        $path = 'identity-sessions/test-session/front.jpg';
        Storage::disk('public')->put($path, 'fake-image');

        Http::fake([
            'api.ocr.space/*' => Http::response([
                'OCRExitCode' => 2,
                'ErrorMessage' => 'E201: Value for parameter is invalid',
            ]),
        ]);

        $service = new OcrSpaceIdentityService;
        $result = $service->extractFromFrontIdPath($path);

        $this->assertFalse($result['success']);
        $this->assertSame(OcrSpaceIdentityService::ERROR_PARSE, $result['error']);
        $this->assertStringNotContainsString('E201', (string) $result['error']);
    }

    public function test_extract_returns_clean_error_for_empty_parsed_text(): void
    {
        Storage::fake('public');
        config([
            'services.ocr_space.api_key' => 'test-api-key',
            'services.ocr_space.endpoint' => 'https://api.ocr.space/parse/image',
        ]);

        $path = 'identity-sessions/test-session/front.jpg';
        Storage::disk('public')->put($path, 'fake-image');

        Http::fake([
            'api.ocr.space/*' => Http::response([
                'OCRExitCode' => 1,
                'ParsedResults' => [
                    ['ParsedText' => ''],
                ],
            ]),
        ]);

        $service = new OcrSpaceIdentityService;
        $result = $service->extractFromFrontIdPath($path);

        $this->assertFalse($result['success']);
        $this->assertSame(OcrSpaceIdentityService::ERROR_EMPTY, $result['error']);
    }

    public function test_extract_returns_clean_error_on_network_failure(): void
    {
        Storage::fake('public');
        config([
            'services.ocr_space.api_key' => 'test-api-key',
            'services.ocr_space.endpoint' => 'https://api.ocr.space/parse/image',
        ]);

        $path = 'identity-sessions/test-session/front.jpg';
        Storage::disk('public')->put($path, 'fake-image');

        Http::fake([
            'api.ocr.space/*' => Http::response([], 500),
        ]);

        $service = new OcrSpaceIdentityService;
        $result = $service->extractFromFrontIdPath($path);

        $this->assertFalse($result['success']);
        $this->assertSame(OcrSpaceIdentityService::ERROR_NETWORK, $result['error']);
    }

    public function test_parse_extracted_fields_returns_empty_values_for_blank_text(): void
    {
        $service = new OcrSpaceIdentityService;

        $result = $service->parseExtractedFields('');

        $this->assertSame('', $result['first_name']);
        $this->assertNull($result['date_of_birth']);
    }
}
