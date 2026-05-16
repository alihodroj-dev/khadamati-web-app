<?php

namespace App\Policies;

use App\Models\Feedback;
use App\Models\User;

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

        return $feedback->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isCitizen();
    }

    public function delete(User $user, Feedback $feedback): bool
    {
        return $user->isAdmin();
    }
}
