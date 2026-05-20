<?php

namespace App\Support;

use Illuminate\Http\Request;

class OfficeValidation
{
    /**
     * Fields office staff may update on their assigned office.
     *
     * @return list<string>
     */
    public static function staffFillable(): array
    {
        return [
            'name',
            'address',
            'phone',
            'email',
            'image_url',
            'latitude',
            'longitude',
            'working_hours',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function staffUpdateRules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'address' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'image_url' => ['nullable', 'string', 'url', 'max:2048'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'working_hours' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function validateStaffUpdate(Request $request): array
    {
        return $request->validate(self::staffUpdateRules());
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public static function onlyStaffFillable(array $validated): array
    {
        return array_intersect_key(
            $validated,
            array_flip(self::staffFillable())
        );
    }

}
