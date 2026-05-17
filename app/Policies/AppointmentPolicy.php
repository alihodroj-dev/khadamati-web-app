<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;
use App\Support\StaffOfficeScope;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isStaff()) {
            $appointment->loadMissing('serviceRequest');

            return StaffOfficeScope::canAccessOffice(
                $user,
                $appointment->serviceRequest?->office_id
            );
        }

        return $appointment->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isCitizen();
    }

    public function update(User $user, Appointment $appointment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isStaff()) {
            $appointment->loadMissing('serviceRequest');

            return StaffOfficeScope::canAccessOffice(
                $user,
                $appointment->serviceRequest?->office_id
            );
        }

        return $appointment->user_id === $user->id
            && $appointment->status === 'scheduled';
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $appointment->user_id === $user->id
            && $appointment->status === 'scheduled';
    }
}