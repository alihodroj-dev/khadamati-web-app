<?php

namespace App\Support;

class RequiredDocumentDefinition
{
    public const DEFAULT_ACCEPTED_TYPES = ['jpg', 'jpeg', 'png', 'pdf'];

    public const DEFAULT_MAX_SIZE_MB = 5;

    /**
     * @return array{
     *     key: string,
     *     label: string,
     *     required: bool,
     *     accepted_types: list<string>,
     *     max_size_mb: int
     * }
     */
    public static function normalize(mixed $entry): ?array
    {
        if (is_array($entry)) {
            return self::fromArray($entry);
        }

        if (is_string($entry) && trim($entry) !== '') {
            return self::fromString($entry);
        }

        return null;
    }

    /**
     * @param  list<mixed>  $entries
     * @return list<array{
     *     key: string,
     *     label: string,
     *     required: bool,
     *     accepted_types: list<string>,
     *     max_size_mb: int
     * }>
     */
    public static function normalizeList(array $entries): array
    {
        $definitions = [];

        foreach ($entries as $entry) {
            $normalized = self::normalize($entry);

            if ($normalized !== null) {
                $definitions[] = $normalized;
            }
        }

        return $definitions;
    }

    /**
     * @param  list<array{
     *     key: string,
     *     label: string,
     *     required: bool,
     *     accepted_types: list<string>,
     *     max_size_mb: int
     * }>  $definitions
     */
    public static function resolveTypeKey(string $documentType, array $definitions): ?string
    {
        $documentType = trim($documentType);

        if ($documentType === '') {
            return null;
        }

        foreach ($definitions as $definition) {
            if (strcasecmp($documentType, $definition['key']) === 0) {
                return $definition['key'];
            }

            if (strcasecmp($documentType, $definition['label']) === 0) {
                return $definition['key'];
            }

            if (self::labelToKey($documentType) === $definition['key']) {
                return $definition['key'];
            }
        }

        return null;
    }

    /**
     * @param  list<array{
     *     key: string,
     *     label: string,
     *     required: bool,
     *     accepted_types: list<string>,
     *     max_size_mb: int
     * }>  $definitions
     * @return array{
     *     key: string,
     *     label: string,
     *     required: bool,
     *     accepted_types: list<string>,
     *     max_size_mb: int
     * }|null
     */
    public static function find(string $documentType, array $definitions): ?array
    {
        $key = self::resolveTypeKey($documentType, $definitions);

        if ($key === null) {
            return null;
        }

        foreach ($definitions as $definition) {
            if ($definition['key'] === $key) {
                return $definition;
            }
        }

        return null;
    }

    public static function labelToKey(string $label): string
    {
        $key = strtolower(trim($label));
        $key = preg_replace('/[^a-z0-9]+/', '_', $key) ?? $key;

        return trim($key, '_');
    }

    /**
     * @param  array<string, mixed>  $entry
     * @return array{
     *     key: string,
     *     label: string,
     *     required: bool,
     *     accepted_types: list<string>,
     *     max_size_mb: int
     * }
     */
    protected static function fromArray(array $entry): ?array
    {
        $label = is_string($entry['label'] ?? null) ? trim($entry['label']) : '';
        $key = is_string($entry['key'] ?? null) && trim($entry['key']) !== ''
            ? self::labelToKey($entry['key'])
            : self::labelToKey($label);

        if ($key === '') {
            return null;
        }

        if ($label === '') {
            $label = self::keyToLabel($key);
        }

        return [
            'key' => $key,
            'label' => $label,
            'required' => (bool) ($entry['required'] ?? true),
            'accepted_types' => self::normalizeAcceptedTypes($entry['accepted_types'] ?? null),
            'max_size_mb' => max(1, (int) ($entry['max_size_mb'] ?? self::DEFAULT_MAX_SIZE_MB)),
        ];
    }

    /**
     * @return array{
     *     key: string,
     *     label: string,
     *     required: bool,
     *     accepted_types: list<string>,
     *     max_size_mb: int
     * }
     */
    protected static function fromString(string $label): array
    {
        $label = trim($label);
        $key = self::labelToKey($label);

        return [
            'key' => $key,
            'label' => $label,
            'required' => true,
            'accepted_types' => self::DEFAULT_ACCEPTED_TYPES,
            'max_size_mb' => self::DEFAULT_MAX_SIZE_MB,
        ];
    }

    /**
     * @return list<string>
     */
    protected static function normalizeAcceptedTypes(mixed $acceptedTypes): array
    {
        if (! is_array($acceptedTypes) || $acceptedTypes === []) {
            return self::DEFAULT_ACCEPTED_TYPES;
        }

        $types = array_values(array_filter($acceptedTypes, fn ($type) => is_string($type) && $type !== ''));

        return $types === [] ? self::DEFAULT_ACCEPTED_TYPES : $types;
    }

    protected static function keyToLabel(string $key): string
    {
        return ucwords(str_replace('_', ' ', $key));
    }
}
