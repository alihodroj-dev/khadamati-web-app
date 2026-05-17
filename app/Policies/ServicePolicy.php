<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;
use App\Support\StaffOfficeScope;

class ServicePolicy
{
    public function view(User $user, Service $service): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isStaff()) {
            return $service->office_id === null
                || StaffOfficeScope::canAccessOffice($user, $service->office_id);
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Service $service): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Service $service): bool
    {
        return $user->isAdmin();
    }
}
