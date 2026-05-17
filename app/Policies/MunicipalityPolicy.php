<?php

namespace App\Policies;

use App\Models\Municipality;
use App\Models\User;

class MunicipalityPolicy
{
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Municipality $municipality): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Municipality $municipality): bool
    {
        return $user->isAdmin();
    }
}
