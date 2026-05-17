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
}
