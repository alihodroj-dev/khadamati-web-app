<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Payment $payment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isStaff()) {
            return $payment->serviceRequest?->assigned_staff_id === $user->id;
        }

        return $payment->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isCitizen();
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->isAdmin();
    }

    public function process(User $user, Payment $payment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isCitizen() && $payment->user_id === $user->id;
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->isAdmin();
    }
}
