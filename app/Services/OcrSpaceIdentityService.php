<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

class OcrSpaceIdentityService
{
    public const ERROR_CONFIGURATION = 'Identity document scanning is unavailable. Please try again later.';

    public const ERROR_NETWORK = 'Unable to reach the document scanning service. Please check your connection and try again.';

    public const ERROR_PARSE = 'Unable to read the ID document. Please upload a clearer photo and try again.';

    public const ERROR_EMPTY = 'No text could be read from the ID document. Please upload a clearer photo and try again.';

    /**
     * @return array{
     *     success: bool,
     *     raw_text: string,
     *     extracted_fields: array{
     *         first_name: string,
     *         last_name: string,
     *         father_name: string,
     *         mother_name: string,
     *         date_of_birth: null|string,
     *         national_id: string
     *     },
     *     error: null|string
     * }
     */
    public function extractFromFrontIdPath(string $filePath): array
    {
        $emptyFields = $this->emptyExtractedFields();

        try {
            $absolutePath = $this->resolveAbsolutePath($filePath);
        } catch (Throwable) {
            return $this->failureResult($emptyFields, self::ERROR_PARSE);
        }

        $apiKey = config('services.ocr_space.api_key');

        if (empty($apiKey)) {
            return $this->failureResult($emptyFields, self::ERROR_CONFIGURATION);
        }

        try {
            $response = $this->callOcrApi($absolutePath, $apiKey);
        } catch (Throwable) {
            return $this->failureResult($emptyFields, self::ERROR_NETWORK);
        }

        $ocrParse = $this->parseOcrApiResponse($response);

        if (! $ocrParse['success']) {
            return $this->failureResult($emptyFields, $ocrParse['error']);
        }

        $rawText = $ocrParse['raw_text'];

        if ($rawText === '') {
            return $this->failureResult($emptyFields, self::ERROR_EMPTY);
        }

        return [
            'success' => true,
            'raw_text' => $rawText,
            'extracted_fields' => $this->parseExtractedFields($rawText),
            'error' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function callOcrApi(string $absolutePath, string $apiKey): array
    {
        $endpoint = config('services.ocr_space.endpoint');
        $fileName = basename($absolutePath);

        $httpResponse = Http::withHeaders([
            'apiKey' => $apiKey,
        ])->attach(
            'file',
            fopen($absolutePath, 'r'),
            $fileName
        )->post($endpoint);

        if (! $httpResponse->successful()) {
            throw new \RuntimeException('OCR HTTP request failed.');
        }

        $payload = $httpResponse->json();

        if (! is_array($payload)) {
            throw new \RuntimeException('OCR response was not valid JSON.');
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $response
     * @return array{success: bool, raw_text: string, error: null|string}
     */
    protected function parseOcrApiResponse(array $response): array
    {
        if (($response['OCRExitCode'] ?? 0) !== 1) {
            return [
                'success' => false,
                'raw_text' => '',
                'error' => $this->mapOcrErrorMessage($response['ErrorMessage'] ?? null),
            ];
        }

        $parsedResults = $response['ParsedResults'] ?? [];

        if (! is_array($parsedResults) || ! isset($parsedResults[0]['ParsedText'])) {
            return [
                'success' => false,
                'raw_text' => '',
                'error' => self::ERROR_EMPTY,
            ];
        }

        $rawText = trim((string) $parsedResults[0]['ParsedText']);

        if ($rawText === '') {
            return [
                'success' => false,
                'raw_text' => '',
                'error' => self::ERROR_EMPTY,
            ];
        }

        return [
            'success' => true,
            'raw_text' => $rawText,
            'error' => null,
        ];
    }

    protected function mapOcrErrorMessage(mixed $errorMessage): string
    {
        if (is_array($errorMessage)) {
            $errorMessage = implode(' ', array_filter($errorMessage, 'is_string'));
        }

        if (! is_string($errorMessage) || trim($errorMessage) === '') {
            return self::ERROR_PARSE;
        }

        return self::ERROR_PARSE;
    }

    /**
     * @return array{
     *     first_name: string,
     *     last_name: string,
     *     father_name: string,
     *     mother_name: string,
     *     date_of_birth: null|string,
     *     national_id: string
     * }
     */
    public function parseExtractedFields(string $rawText): array
    {
        $data = $this->emptyExtractedFields();
        $rawText = trim($rawText);

        if ($rawText === '') {
            return $data;
        }

        $data['national_id'] = $this->extractNationalId($rawText) ?? '';
        $data['date_of_birth'] = $this->extractDateOfBirth($rawText);

        $labeledNames = $this->extractLabeledNames($rawText);
        $data = array_merge($data, $labeledNames);

        if ($data['first_name'] === '' && $data['last_name'] === '') {
            $data = array_merge($data, $this->extractNamesFromLines($rawText));
        }

        return $data;
    }

    /**
     * @return array{
     *     first_name: string,
     *     last_name: string,
     *     father_name: string,
     *     mother_name: string,
     *     date_of_birth: null|string,
     *     national_id: string
     * }
     */
    public function emptyExtractedFields(): array
    {
        return [
            'first_name' => '',
            'last_name' => '',
            'father_name' => '',
            'mother_name' => '',
            'date_of_birth' => null,
            'national_id' => '',
        ];
    }

    protected function resolveAbsolutePath(string $filePath): string
    {
        if (is_file($filePath)) {
            return $filePath;
        }

        $storagePath = Storage::disk('public')->path($filePath);

        if (is_file($storagePath)) {
            return $storagePath;
        }

        throw new \InvalidArgumentException('ID front image file was not found.');
    }

    protected function extractNationalId(string $rawText): ?string
    {
        if (preg_match('/\bnational\s*(?:id|no\.?|number)?\s*[:\-]?\s*([A-Z0-9\-]+)/i', $rawText, $matches)) {
            return trim($matches[1]);
        }

        if (preg_match('/\b(\d{8,})\b/', $rawText, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    protected function extractDateOfBirth(string $rawText): ?string
    {
        if (preg_match('/\b(?:dob|date\s*of\s*birth|birth\s*date)\s*[:\-]?\s*(\d{4}[-\/.]\d{2}[-\/.]\d{2})/i', $rawText, $matches)) {
            return $this->normalizeDate($matches[1]);
        }

        if (preg_match('/\b(\d{4}[-\/.]\d{2}[-\/.]\d{2})\b/', $rawText, $matches)) {
            return $this->normalizeDate($matches[1]);
        }

        if (preg_match('/\b(\d{2}[-\/.]\d{2}[-\/.]\d{4})\b/', $rawText, $matches)) {
            return $this->normalizeDate($matches[1]);
        }

        return null;
    }

    /**
     * @return array{
     *     first_name: string,
     *     last_name: string,
     *     father_name: string,
     *     mother_name: string
     * }
     */
    protected function extractLabeledNames(string $rawText): array
    {
        return [
            'first_name' => $this->matchLabeledValue($rawText, 'first') ?? '',
            'last_name' => $this->matchLabeledValue($rawText, 'last') ?? '',
            'father_name' => $this->matchLabeledValue($rawText, 'father') ?? '',
            'mother_name' => $this->matchLabeledValue($rawText, 'mother') ?? '',
        ];
    }

    protected function matchLabeledValue(string $rawText, string $label): ?string
    {
        $pattern = '/\b'.preg_quote($label, '/').'\s*name\s*[:\-]?\s*(.+)$/im';

        if (preg_match($pattern, $rawText, $matches)) {
            return trim(preg_split('/\R/', $matches[1])[0]);
        }

        return null;
    }

    /**
     * @return array{
     *     first_name: string,
     *     last_name: string,
     *     father_name: string,
     *     mother_name: string
     * }
     */
    protected function extractNamesFromLines(string $rawText): array
    {
        $lines = collect(preg_split('/\R+/', $rawText))
            ->map(fn (string $line) => trim($line))
            ->filter(fn (string $line) => $line !== '' && ! preg_match('/^\d+$/', $line))
            ->values();

        $firstName = '';
        $lastName = '';
        $fatherName = '';
        $motherName = '';

        if ($lines->count() >= 1) {
            $nameParts = preg_split('/\s+/', (string) $lines->get(0)) ?: [];

            if (count($nameParts) >= 2) {
                $firstName = $nameParts[0];
                $lastName = $nameParts[1];
            } elseif (count($nameParts) === 1) {
                $firstName = $nameParts[0];
            }
        }

        if ($lines->count() >= 2) {
            $fatherName = (string) $lines->get(1);
        }

        if ($lines->count() >= 3) {
            $motherName = (string) $lines->get(2);
        }

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'father_name' => $fatherName,
            'mother_name' => $motherName,
        ];
    }

    protected function normalizeDate(string $value): string
    {
        $value = str_replace(['.', '/'], '-', trim($value));

        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $value, $matches)) {
            return sprintf('%s-%s-%s', $matches[3], $matches[2], $matches[1]);
        }

        return $value;
    }

    /**
     * @param  array{
     *     first_name: string,
     *     last_name: string,
     *     father_name: string,
     *     mother_name: string,
     *     date_of_birth: null|string,
     *     national_id: string
     * }  $extractedFields
     * @return array{
     *     success: bool,
     *     raw_text: string,
     *     extracted_fields: array{
     *         first_name: string,
     *         last_name: string,
     *         father_name: string,
     *         mother_name: string,
     *         date_of_birth: null|string,
     *         national_id: string
     *     },
     *     error: null|string
     * }
     */
    protected function failureResult(array $extractedFields, string $error): array
    {
        return [
            'success' => false,
            'raw_text' => '',
            'extracted_fields' => $extractedFields,
            'error' => $error,
        ];
    }
}
