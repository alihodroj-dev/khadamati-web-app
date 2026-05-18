<?php

namespace App\Support;

class AdminFormInput
{
    /**
     * @return list<string>
     */
    public static function parseRequiredDocuments(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (string $item) => trim($item),
            explode(',', $value)
        )));
    }

    public static function boolean(mixed $value, bool $default = true): bool
    {
        if ($value === null || $value === '') {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return array<string, mixed>|null
     */
    /**
     * @param  list<mixed>|null  $documents
     */
    public static function formatRequiredDocumentsForForm(?array $documents): string
    {
        if ($documents === null || $documents === []) {
            return '';
        }

        $keys = [];

        foreach ($documents as $document) {
            if (is_array($document) && isset($document['key'])) {
                $keys[] = (string) $document['key'];
            } elseif (is_string($document) && trim($document) !== '') {
                $keys[] = trim($document);
            }
        }

        return implode(', ', $keys);
    }

    public static function parseWorkingHours(mixed $value): ?array
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return null;
        }

        if (is_array($value)) {
            return self::parseWorkingHoursArray($value);
        }

        if (! is_string($value)) {
            return null;
        }

        $decoded = json_decode($value, true);

        if (! is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $decoded;
    }

    public static function workingHoursInputHasValue(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (! is_array($value)) {
            return true;
        }

        foreach ($value as $dayInput) {
            if (! is_array($dayInput)) {
                return true;
            }

            if (self::boolean($dayInput['enabled'] ?? false, false)
                || trim((string) ($dayInput['start'] ?? '')) !== ''
                || trim((string) ($dayInput['end'] ?? '')) !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $value
     * @return array<string, array{0: string, 1: string}>|null
     */
    protected static function parseWorkingHoursArray(array $value): ?array
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $hours = [];

        foreach ($days as $day) {
            $dayInput = $value[$day] ?? [];

            if (! is_array($dayInput)) {
                return null;
            }

            $enabled = self::boolean($dayInput['enabled'] ?? false, false);
            $start = trim((string) ($dayInput['start'] ?? ''));
            $end = trim((string) ($dayInput['end'] ?? ''));

            if (! $enabled && $start === '' && $end === '') {
                continue;
            }

            if (! $enabled) {
                continue;
            }

            if (! self::isValidClockTime($start) || ! self::isValidClockTime($end) || $start >= $end) {
                return null;
            }

            $hours[$day] = [$start, $end];
        }

        return $hours === [] ? null : $hours;
    }

    protected static function isValidClockTime(string $value): bool
    {
        return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $value) === 1;
    }
}
