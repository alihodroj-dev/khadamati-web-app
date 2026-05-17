<?php

namespace App\Policies;

use App\Models\Feedback;
use App\Models\User;
use App\Support\ServiceRequestStatus;
use App\Support\StaffOfficeScope;

class FeedbackPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Feedback $feedback): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isStaff()) {
            $feedback->loadMissing('serviceRequest');

            return $this->staffCanAccessCompletedFeedback($user, $feedback);
        }

        return $feedback->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isCitizen();
    }

    public function update(User $user, Feedback $feedback): bool
    {
        return $user->isCitizen() && $feedback->user_id === $user->id;
    }

    public function delete(User $user, Feedback $feedback): bool
    {
        return $user->isAdmin();
    }

    public function respond(User $user, Feedback $feedback): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->isStaff()) {
            return false;
        }

        return $this->staffCanAccessCompletedFeedback($user, $feedback);
    }

    private function staffCanAccessCompletedFeedback(User $user, Feedback $feedback): bool
    {
        return StaffOfficeScope::canAccessOffice(
            $user,
            $feedback->serviceRequest?->office_id
        ) && $feedback->serviceRequest?->status === ServiceRequestStatus::COMPLETED;
    }
}
