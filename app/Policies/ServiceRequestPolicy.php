<?php

namespace App\Policies;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\StaffOfficeScope;

class ServiceRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    public function view(User $user, ServiceRequest $serviceRequest): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isStaff()) {
            return StaffOfficeScope::canAccessOffice($user, $serviceRequest->office_id);
        }

        return $serviceRequest->user_id === $user->id;
    }

    public function updateStatus(User $user, ServiceRequest $serviceRequest): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isStaff()
            && StaffOfficeScope::canAccessOffice($user, $serviceRequest->office_id)
            && (int) $serviceRequest->assigned_staff_id === $user->id;
    }

    public function assign(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->isAdmin();
    }
}
