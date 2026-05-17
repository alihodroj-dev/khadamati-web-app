<?php

namespace App\Services;

use App\Support\ArabicTransliterator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
            Log::warning('OCR.space skipped: OCR_SPACE_API_KEY is not configured.');

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

        $extractedFields = $this->parseExtractedFields($rawText);

        Log::info('OCR.space extracted identity fields.', [
            'file_path' => $filePath,
            'raw_text' => $rawText,
            'extracted_fields' => $extractedFields,
        ]);

        return [
            'success' => true,
            'raw_text' => $rawText,
            'extracted_fields' => $extractedFields,
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

        // OCR.space expects multipart text fields as strings (see their Postman collection).
        $httpResponse = Http::withHeaders([
            'apikey' => $apiKey,
        ])->attach(
            'file',
            fopen($absolutePath, 'r'),
            $fileName
        )->post($endpoint, [
            'language' => (string) config('services.ocr_space.language', 'auto'),
            'isOverlayRequired' => 'false',
            'OCREngine' => (string) config('services.ocr_space.engine', 3),
        ]);

        if (! $httpResponse->successful()) {
            Log::warning('OCR.space HTTP request failed.', [
                'file' => $fileName,
                'http_status' => $httpResponse->status(),
                'body' => $httpResponse->body(),
            ]);

            throw new \RuntimeException('OCR HTTP request failed.');
        }

        $payload = $httpResponse->json();

        if (! is_array($payload)) {
            Log::warning('OCR.space response was not valid JSON.', [
                'file' => $fileName,
                'body' => $httpResponse->body(),
            ]);

            throw new \RuntimeException('OCR response was not valid JSON.');
        }

        Log::info('OCR.space API response.', [
            'file' => $fileName,
            'language' => config('services.ocr_space.language'),
            'engine' => config('services.ocr_space.engine'),
            'response' => $payload,
        ]);

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $response
     * @return array{success: bool, raw_text: string, error: null|string}
     */
    protected function parseOcrApiResponse(array $response): array
    {
        if (($response['OCRExitCode'] ?? 0) !== 1) {
            Log::warning('OCR.space processing error.', [
                'ocr_exit_code' => $response['OCRExitCode'] ?? null,
                'error_message' => $response['ErrorMessage'] ?? null,
            ]);

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

        if ($this->containsArabic($rawText)) {
            $data = $this->parseLebaneseIdArabicText($rawText);
        } else {
            $data['national_id'] = $this->extractNationalId($rawText) ?? '';
            $data['date_of_birth'] = $this->extractDateOfBirth($rawText);

            $labeledNames = $this->extractLabeledNames($rawText);
            $data = array_merge($data, $labeledNames);

            if ($data['first_name'] === '' && $data['last_name'] === '') {
                $data = array_merge($data, $this->extractNamesFromLines($rawText));
            }
        }

        return $this->transliterateExtractedFields($data);
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
    public function parseLebaneseIdArabicText(string $rawText): array
    {
        $data = $this->emptyExtractedFields();

        $data['first_name'] = $this->extractArabicLabeledValue($rawText, [
            'الاسم',
        ]) ?? '';
        $data['last_name'] = $this->extractArabicLabeledValue($rawText, [
            'الشهرة',
        ]) ?? '';
        $data['father_name'] = $this->extractArabicLabeledValue($rawText, [
            'اسم الأب',
            'اسم الاب',
        ]) ?? '';
        $data['mother_name'] = $this->extractArabicLabeledValue($rawText, [
            'اسم الام وشهرتها',
            'اسم الأم وشهرتها',
            'اسم الام',
            'اسم الأم',
        ]) ?? '';

        $dateValue = $this->extractArabicLabeledValue($rawText, [
            'تاريخ الولادة',
        ]);

        if ($dateValue !== null) {
            $data['date_of_birth'] = $this->normalizeArabicDate($dateValue);
        }

        $data['national_id'] = $this->extractArabicNationalId($rawText) ?? '';

        return $data;
    }

    protected function containsArabic(string $text): bool
    {
        return (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $text);
    }

    /**
     * @param  list<string>  $labels
     */
    protected function extractArabicLabeledValue(string $rawText, array $labels): ?string
    {
        foreach ($labels as $label) {
            $escapedLabel = preg_quote($label, '/');
            $pattern = '/'.$escapedLabel.'\s*[：\:]\s*([^\r\n]+)/u';

            if (preg_match($pattern, $rawText, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    protected function extractArabicNationalId(string $rawText): ?string
    {
        $lines = preg_split('/\R+/', $rawText) ?: [];

        for ($index = count($lines) - 1; $index >= 0; $index--) {
            $line = trim(ArabicTransliterator::normalizeDigits((string) $lines[$index]));
            $line = preg_replace('/\s+/u', '', $line) ?? '';

            if ($line !== '' && preg_match('/^\d{8,}$/', $line)) {
                return $line;
            }
        }

        $normalized = ArabicTransliterator::normalizeDigits($rawText);

        if (preg_match('/\b(\d{8,})\b/', $normalized, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function normalizeArabicDate(string $value): ?string
    {
        $value = ArabicTransliterator::normalizeDigits(trim($value));
        $value = str_replace(['.', '/'], '-', $value);

        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $value, $matches)) {
            return sprintf(
                '%04d-%02d-%02d',
                (int) $matches[3],
                (int) $matches[2],
                (int) $matches[1]
            );
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $matches)) {
            return sprintf('%s-%s-%s', $matches[1], $matches[2], $matches[3]);
        }

        return $this->normalizeDate($value);
    }

    /**
     * @param  array{
     *     first_name: string,
     *     last_name: string,
     *     father_name: string,
     *     mother_name: string,
     *     date_of_birth: null|string,
     *     national_id: string
     * }  $data
     * @return array{
     *     first_name: string,
     *     last_name: string,
     *     father_name: string,
     *     mother_name: string,
     *     date_of_birth: null|string,
     *     national_id: string
     * }
     */
    protected function transliterateExtractedFields(array $data): array
    {
        $data['first_name'] = ArabicTransliterator::transliterate($data['first_name']);
        $data['last_name'] = ArabicTransliterator::transliterate($data['last_name']);
        $data['father_name'] = ArabicTransliterator::transliterate($data['father_name']);
        $data['mother_name'] = ArabicTransliterator::transliterate($data['mother_name']);
        $data['national_id'] = ArabicTransliterator::normalizeDigits($data['national_id']);

        if ($data['date_of_birth'] !== null) {
            $data['date_of_birth'] = $this->normalizeArabicDate($data['date_of_birth'])
                ?? $data['date_of_birth'];
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
        Log::info('Identity OCR failed.', ['error' => $error]);

        return [
            'success' => false,
            'raw_text' => '',
            'extracted_fields' => $extractedFields,
            'error' => $error,
        ];
    }
}
