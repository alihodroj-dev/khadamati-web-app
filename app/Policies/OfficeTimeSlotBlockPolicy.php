<?php

namespace App\Policies;

use App\Models\OfficeTimeSlotBlock;
use App\Models\User;
use App\Support\StaffOfficeScope;

class OfficeTimeSlotBlockPolicy
{
    public function viewAny(User $user): bool
    {
        return StaffOfficeScope::appliesTo($user);
    }

    public function view(User $user, OfficeTimeSlotBlock $officeTimeSlotBlock): bool
    {
        return StaffOfficeScope::canAccessOffice($user, $officeTimeSlotBlock->office_id);
    }

    public function create(User $user): bool
    {
        return StaffOfficeScope::appliesTo($user);
    }

    public function delete(User $user, OfficeTimeSlotBlock $officeTimeSlotBlock): bool
    {
        return StaffOfficeScope::canAccessOffice($user, $officeTimeSlotBlock->office_id);
    }
}
