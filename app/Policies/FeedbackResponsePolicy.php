<?php

namespace App\Policies;

use App\Models\FeedbackResponse;
use App\Models\User;
use App\Support\ServiceRequestStatus;
use App\Support\StaffOfficeScope;

class FeedbackResponsePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    public function view(User $user, FeedbackResponse $feedbackResponse): bool
    {
        return $this->canManageFeedback($user, $feedbackResponse);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    public function update(User $user, FeedbackResponse $feedbackResponse): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isStaff()
            && (int) $feedbackResponse->responder_id === (int) $user->id
            && $this->canManageFeedback($user, $feedbackResponse);
    }

    public function delete(User $user, FeedbackResponse $feedbackResponse): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isStaff()
            && (int) $feedbackResponse->responder_id === (int) $user->id
            && $this->canManageFeedback($user, $feedbackResponse);
    }

    private function canManageFeedback(User $user, FeedbackResponse $feedbackResponse): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->isStaff()) {
            return false;
        }

        $feedbackResponse->loadMissing('feedback.serviceRequest');

        $serviceRequest = $feedbackResponse->feedback?->serviceRequest;

        if ($serviceRequest === null) {
            return false;
        }

        return StaffOfficeScope::canAccessOffice($user, $serviceRequest->office_id)
            && $serviceRequest->status === ServiceRequestStatus::COMPLETED;
    }
}
