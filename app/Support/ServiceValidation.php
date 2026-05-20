<?php

namespace App\Support;

use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ServiceValidation
{
    /**
     * @return array<string, mixed>
     */
    public static function validate(Request $request, User $user, bool $partial = false, ?Service $service = null): array
    {
        $required = $partial ? 'sometimes' : 'required';

        $rules = [
            'service_category_id' => [$required, 'exists:service_categories,id'],
            'name' => [$required, 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string', 'url', 'max:2048'],
            'base_fee' => [$partial ? 'sometimes' : 'required', 'numeric', 'min:0'],
            'estimated_processing_days' => ['nullable', 'integer', 'min:0'],
            'required_documents' => ['nullable', 'array'],
            'required_documents.*' => ['nullable'],
            'requires_appointment' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];

        if ($user->isAdmin()) {
            $rules['office_id'] = ['nullable', 'integer', 'exists:offices,id'];
        }

        $validated = $request->validate($rules);

        if ($user->isStaff() && $service !== null && $service->office_id === null) {
            throw ValidationException::withMessages([
                'service' => ['You cannot modify global services.'],
            ]);
        }

        $officeId = self::resolveOfficeId($user, $validated, $service);

        if ($user->isStaff() && $officeId === null) {
            throw ValidationException::withMessages([
                'office_id' => ['Staff users must be assigned to an office to manage services.'],
            ]);
        }

        $payload = [];

        if (array_key_exists('service_category_id', $validated)) {
            $payload['service_category_id'] = (int) $validated['service_category_id'];
        }

        if (array_key_exists('name', $validated)) {
            $payload['name'] = $validated['name'];
        }

        if (array_key_exists('description', $validated)) {
            $payload['description'] = $validated['description'];
        }

        if (array_key_exists('image_url', $validated)) {
            $payload['image_url'] = $validated['image_url'];
        }

        if (array_key_exists('base_fee', $validated)) {
            $payload['base_fee'] = $validated['base_fee'];
        }

        if (array_key_exists('estimated_processing_days', $validated)) {
            $payload['estimated_processing_days'] = $validated['estimated_processing_days'];
        }

        if (array_key_exists('required_documents', $validated)) {
            $payload['required_documents'] = RequiredDocumentDefinition::normalizeList(
                $validated['required_documents'] ?? []
            );
        }

        if (array_key_exists('requires_appointment', $validated)) {
            $payload['requires_appointment'] = (bool) $validated['requires_appointment'];
        }

        if (array_key_exists('is_active', $validated)) {
            $payload['is_active'] = (bool) $validated['is_active'];
        }

        if (! $partial || $user->isStaff() || array_key_exists('office_id', $validated)) {
            $payload['office_id'] = $officeId;
        }

        if (! $partial) {
            return [
                'service_category_id' => (int) $validated['service_category_id'],
                'office_id' => $officeId,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'image_url' => $validated['image_url'] ?? null,
                'base_fee' => $validated['base_fee'],
                'estimated_processing_days' => $validated['estimated_processing_days'] ?? null,
                'required_documents' => RequiredDocumentDefinition::normalizeList(
                    $validated['required_documents'] ?? []
                ),
                'requires_appointment' => (bool) ($validated['requires_appointment'] ?? false),
                'is_active' => (bool) ($validated['is_active'] ?? true),
            ];
        }

        return $payload;
    }

    public static function resolveOfficeId(User $user, array $validated, ?Service $service = null): ?int
    {
        if ($user->isAdmin()) {
            if (array_key_exists('office_id', $validated)) {
                return $validated['office_id'] !== null && $validated['office_id'] !== ''
                    ? (int) $validated['office_id']
                    : null;
            }

            return $service?->office_id;
        }

        if ($user->isStaff()) {
            return $user->office_id !== null ? (int) $user->office_id : null;
        }

        return null;
    }
}
