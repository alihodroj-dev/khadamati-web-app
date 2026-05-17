<?php

namespace App\Policies;

use App\Models\OfficeTimeSlot;
use App\Models\User;
use App\Support\StaffOfficeScope;

class OfficeTimeSlotPolicy
{
    public function viewAny(User $user): bool
    {
        return StaffOfficeScope::appliesTo($user);
    }

    public function view(User $user, OfficeTimeSlot $officeTimeSlot): bool
    {
        return StaffOfficeScope::canAccessOffice($user, $officeTimeSlot->office_id);
    }

    public function create(User $user): bool
    {
        return StaffOfficeScope::appliesTo($user);
    }

    public function update(User $user, OfficeTimeSlot $officeTimeSlot): bool
    {
        return StaffOfficeScope::canAccessOffice($user, $officeTimeSlot->office_id);
    }

    public function delete(User $user, OfficeTimeSlot $officeTimeSlot): bool
    {
        return StaffOfficeScope::canAccessOffice($user, $officeTimeSlot->office_id);
    }
}
