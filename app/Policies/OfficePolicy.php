<?php

namespace App\Policies;

use App\Models\Office;
use App\Models\User;

class OfficePolicy
{
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Office $office): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isStaff()
            && $user->office_id !== null
            && (int) $user->office_id === (int) $office->id;
    }

    public function delete(User $user, Office $office): bool
    {
        return $user->isAdmin();
    }
}
