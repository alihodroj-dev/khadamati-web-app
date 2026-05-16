<?php

namespace Tests\Unit;

use App\Services\OcrSpaceService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OcrSpaceServiceTest extends TestCase
{
    public function test_parse_image_sends_multipart_request_with_api_key_header(): void
    {
        config([
            'services.ocr_space.api_key' => 'test-api-key',
            'services.ocr_space.endpoint' => 'https://api.ocr.space/parse/image',
        ]);

        Http::fake([
            'api.ocr.space/*' => Http::response([
                'OCRExitCode' => 1,
                'ParsedResults' => [
                    ['ParsedText' => 'ID 123456789'],
                ],
            ]),
        ]);

        $file = UploadedFile::fake()->image('id-card.jpg');
        $service = new OcrSpaceService;

        $text = $service->extractText($file);

        $this->assertSame('ID 123456789', $text);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.ocr.space/parse/image'
                && $request->hasHeader('apiKey', 'test-api-key')
                && $request->method() === 'POST';
        });
    }

    public function test_parse_image_throws_when_api_key_missing(): void
    {
        config([
            'services.ocr_space.api_key' => null,
            'services.ocr_space.endpoint' => 'https://api.ocr.space/parse/image',
        ]);

        $file = UploadedFile::fake()->image('id-card.jpg');
        $service = new OcrSpaceService;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('OCR.space API key is not configured.');

        $service->extractText($file);
    }
}
