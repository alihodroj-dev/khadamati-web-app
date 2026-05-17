<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserOfficeAssignment
{
    /**
     * @return array<string, list<mixed>>
     */
    public static function officeIdRules(Request $request, ?User $user = null): array
    {
        return [
            'office_id' => [
                Rule::requiredIf(fn () => $request->input('role', $user?->role) === User::ROLE_STAFF),
                'nullable',
                'integer',
                'exists:offices,id',
            ],
        ];
    }

    public static function resolveOfficeId(string $role, mixed $officeId): ?int
    {
        if ($role !== User::ROLE_STAFF || $officeId === null || $officeId === '') {
            return null;
        }

        return (int) $officeId;
    }
}
