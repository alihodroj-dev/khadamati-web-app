<?php

namespace App\Policies;

use App\Models\ServiceRequest;
use App\Models\User;

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
            return $serviceRequest->assigned_staff_id === $user->id;
        }

        return $serviceRequest->user_id === $user->id;
    }

    public function updateStatus(User $user, ServiceRequest $serviceRequest): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isStaff() && $serviceRequest->assigned_staff_id === $user->id;
    }

    public function assign(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->isAdmin();
    }
}
