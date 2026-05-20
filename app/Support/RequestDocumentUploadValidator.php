<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rules\File;

class RequestDocumentUploadValidator
{
    /**
     * iOS camera photos are often HEIC; include common extensions explicitly.
     *
     * @return list<string>
     */
    public static function defaultAcceptedTypes(): array
    {
        return ['jpg', 'jpeg', 'png', 'pdf', 'heic', 'heif'];
    }

    /**
     * @param  array{
     *     key: string,
     *     label: string,
     *     required: bool,
     *     accepted_types: list<string>,
     *     max_size_mb: int
     * }|null  $definition
     * @return list<string>
     */
    public static function acceptedTypes(?array $definition): array
    {
        $types = $definition['accepted_types'] ?? RequiredDocumentDefinition::DEFAULT_ACCEPTED_TYPES;

        return self::normalizeExtensionList($types);
    }

    /**
     * @param  array{
     *     key: string,
     *     label: string,
     *     required: bool,
     *     accepted_types: list<string>,
     *     max_size_mb: int
     * }|null  $definition
     */
    public static function maxSizeKilobytes(?array $definition): int
    {
        $maxMb = $definition['max_size_mb'] ?? RequiredDocumentDefinition::DEFAULT_MAX_SIZE_MB;

        return max(1, (int) $maxMb) * 1024;
    }

    /**
     * @param  array{
     *     key: string,
     *     label: string,
     *     required: bool,
     *     accepted_types: list<string>,
     *     max_size_mb: int
     * }|null  $definition
     * @return list<\Illuminate\Contracts\Validation\ValidationRule|string>
     */
    public static function fileRules(string $attribute, ?array $definition): array
    {
        return [
            $attribute => [
                File::types(self::acceptedTypes($definition))
                    ->max(self::maxSizeKilobytes($definition)),
            ],
        ];
    }

    /**
     * Detect PHP upload errors before Laravel's generic "failed to upload" message.
     *
     * @return array<string, list<string>>|null
     */
    public static function earlyUploadErrors(?UploadedFile $file, string $attribute = 'document'): ?array
    {
        if ($file === null) {
            return [
                $attribute => [
                    'No file was received. Use multipart/form-data and send the file under the correct field name.',
                ],
            ];
        }

        if (! $file->isValid()) {
            return [
                $attribute => [self::uploadErrorMessage($file)],
            ];
        }

        return null;
    }

    public static function uploadErrorMessage(UploadedFile $file): string
    {
        return match ($file->getError()) {
            UPLOAD_ERR_INI_SIZE => sprintf(
                'The file exceeds the server upload limit (%s). Use a smaller file or ask your administrator to raise upload_max_filesize (app allows up to %d MB).',
                ini_get('upload_max_filesize') ?: 'unknown',
                RequiredDocumentDefinition::DEFAULT_MAX_SIZE_MB
            ),
            UPLOAD_ERR_FORM_SIZE => sprintf(
                'The file exceeds the maximum allowed size (%d MB).',
                RequiredDocumentDefinition::DEFAULT_MAX_SIZE_MB
            ),
            UPLOAD_ERR_PARTIAL => 'The file was only partially uploaded. Please try again.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_EXTENSION => 'The server could not store the uploaded file. Please try again later.',
            default => 'The file failed to upload. Please try again.',
        };
    }

    /**
     * @param  list<string>  $types
     * @return list<string>
     */
    protected static function normalizeExtensionList(array $types): array
    {
        $normalized = [];

        foreach ($types as $type) {
            if (! is_string($type) || $type === '') {
                continue;
            }

            $type = strtolower(ltrim($type, '.'));

            if (str_contains($type, '/')) {
                $type = match ($type) {
                    'image/jpeg' => 'jpeg',
                    'image/jpg' => 'jpg',
                    'image/png' => 'png',
                    'image/heic' => 'heic',
                    'image/heif' => 'heif',
                    'application/pdf' => 'pdf',
                    default => null,
                };

                if ($type === null) {
                    continue;
                }
            }

            $normalized[] = $type;
        }

        $normalized = array_values(array_unique($normalized));

        return $normalized === [] ? self::defaultAcceptedTypes() : $normalized;
    }
}
