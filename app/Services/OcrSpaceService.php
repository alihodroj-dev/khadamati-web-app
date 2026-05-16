<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OcrSpaceService
{
    /**
     * @return array<string, mixed>
     */
    public function parseImage(UploadedFile $file): array
    {
        $apiKey = config('services.ocr_space.api_key');
        $endpoint = config('services.ocr_space.endpoint');

        if (empty($apiKey)) {
            throw new RuntimeException('OCR.space API key is not configured.');
        }

        $response = Http::withHeaders([
            'apiKey' => $apiKey,
        ])->attach(
            'file',
            fopen($file->getRealPath(), 'r'),
            $file->getClientOriginalName()
        )->post($endpoint);

        if (! $response->successful()) {
            throw new RuntimeException('OCR.space request failed.');
        }

        $payload = $response->json();

        if (! is_array($payload) || ($payload['OCRExitCode'] ?? 0) !== 1) {
            $message = is_array($payload)
                ? ($payload['ErrorMessage'] ?? 'Unable to parse the document.')
                : 'Unable to parse the document.';

            if (is_array($message)) {
                $message = implode(' ', $message);
            }

            throw new RuntimeException((string) $message);
        }

        return $payload;
    }

    public function extractText(UploadedFile $file): string
    {
        $payload = $this->parseImage($file);
        $parsedResults = $payload['ParsedResults'] ?? [];

        if (! is_array($parsedResults)) {
            return '';
        }

        $text = collect($parsedResults)
            ->pluck('ParsedText')
            ->filter(fn ($value) => is_string($value) && $value !== '')
            ->implode("\n");

        return trim($text);
    }
}
