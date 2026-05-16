<?php

namespace App\Support;

use App\Models\User;

class CitizenDisplayName
{
    public static function fromUser(?User $user): string
    {
        $name = $user?->name;

        if (! $name) {
            return 'Citizen';
        }

        $firstName = explode(' ', trim($name))[0];

        return $firstName !== '' ? $firstName : 'Citizen';
    }
}
