<?php

namespace App\Services;

class IdentityIdParserService
{
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
    public function parse(string $ocrText): array
    {
        $data = $this->emptyExtractedData();

        $ocrText = trim($ocrText);

        if ($ocrText === '') {
            return $data;
        }

        $data['national_id'] = $this->extractNationalId($ocrText) ?? '';
        $data['date_of_birth'] = $this->extractDateOfBirth($ocrText);

        $labeledNames = $this->extractLabeledNames($ocrText);
        $data = array_merge($data, $labeledNames);

        if ($data['first_name'] === '' && $data['last_name'] === '') {
            $lineNames = $this->extractNamesFromLines($ocrText);
            $data = array_merge($data, $lineNames);
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
    public function emptyExtractedData(): array
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

    protected function extractNationalId(string $ocrText): ?string
    {
        if (preg_match('/\bnational\s*(?:id|no\.?|number)?\s*[:\-]?\s*([A-Z0-9\-]+)/i', $ocrText, $matches)) {
            return trim($matches[1]);
        }

        if (preg_match('/\b(\d{8,})\b/', $ocrText, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    protected function extractDateOfBirth(string $ocrText): ?string
    {
        if (preg_match('/\b(?:dob|date\s*of\s*birth|birth\s*date)\s*[:\-]?\s*(\d{4}[-\/.]\d{2}[-\/.]\d{2})/i', $ocrText, $matches)) {
            return $this->normalizeDate($matches[1]);
        }

        if (preg_match('/\b(\d{4}[-\/.]\d{2}[-\/.]\d{2})\b/', $ocrText, $matches)) {
            return $this->normalizeDate($matches[1]);
        }

        if (preg_match('/\b(\d{2}[-\/.]\d{2}[-\/.]\d{4})\b/', $ocrText, $matches)) {
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
    protected function extractLabeledNames(string $ocrText): array
    {
        return [
            'first_name' => $this->matchLabeledValue($ocrText, 'first') ?? '',
            'last_name' => $this->matchLabeledValue($ocrText, 'last') ?? '',
            'father_name' => $this->matchLabeledValue($ocrText, 'father') ?? '',
            'mother_name' => $this->matchLabeledValue($ocrText, 'mother') ?? '',
        ];
    }

    protected function matchLabeledValue(string $ocrText, string $label): ?string
    {
        $pattern = '/\b'.preg_quote($label, '/').'\s*name\s*[:\-]?\s*(.+)$/im';

        if (preg_match($pattern, $ocrText, $matches)) {
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
    protected function extractNamesFromLines(string $ocrText): array
    {
        $lines = collect(preg_split('/\R+/', $ocrText))
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
}
