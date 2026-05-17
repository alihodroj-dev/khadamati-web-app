<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OfficeTimeSlotValidation
{
    /**
     * @return array<string, mixed>
     */
    public static function slotRules(bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return [
            'staff_id' => ['nullable', 'integer', 'exists:users,id'],
            'day_of_week' => [$required, 'integer', 'between:0,6'],
            'start_time' => [$required, 'date_format:H:i'],
            'end_time' => [$required, 'date_format:H:i', 'after:start_time'],
            'slot_duration_minutes' => ['nullable', 'integer', 'min:5', 'max:240'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function blockRules(): array
    {
        return [
            'staff_id' => ['nullable', 'integer', 'exists:users,id'],
            'date' => ['required', 'date', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    public static function assertStaffBelongsToOffice(?int $staffId, int $officeId): void
    {
        if ($staffId === null) {
            return;
        }

        $staff = User::query()->find($staffId);

        if ($staff === null || ! $staff->isStaff() || (int) $staff->office_id !== $officeId) {
            throw ValidationException::withMessages([
                'staff_id' => ['The selected staff member must belong to this office.'],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public static function normalizeSlotPayload(array $validated): array
    {
        if (isset($validated['start_time'])) {
            $validated['start_time'] = self::normalizeTimeForStorage($validated['start_time']);
        }

        if (isset($validated['end_time'])) {
            $validated['end_time'] = self::normalizeTimeForStorage($validated['end_time']);
        }

        if (array_key_exists('slot_duration_minutes', $validated) && $validated['slot_duration_minutes'] === null) {
            $validated['slot_duration_minutes'] = 30;
        }

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public static function normalizeBlockPayload(array $validated): array
    {
        $validated['start_time'] = self::normalizeTimeForStorage($validated['start_time']);
        $validated['end_time'] = self::normalizeTimeForStorage($validated['end_time']);

        return $validated;
    }

    public static function normalizeTimeForStorage(string $time): string
    {
        return strlen($time) === 5 ? $time.':00' : $time;
    }
}
