<?php

namespace App\Support;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ServiceRequestAssignment
{
    public static function assign(ServiceRequest $serviceRequest, User $staffUser): ServiceRequest
    {
        if (! $staffUser->isStaff()) {
            throw ValidationException::withMessages([
                'staff_id' => ['The selected user is not a staff member.'],
            ]);
        }

        // FIX: If request has no office, auto-assign the staff's office
        if ($serviceRequest->office_id === null) {
            $serviceRequest->office_id = $staffUser->office_id;
            $serviceRequest->save();
        }
        
        // Now check if offices match (after potentially updating)
        if ((int) $staffUser->office_id !== (int) $serviceRequest->office_id) {
            throw ValidationException::withMessages([
                'staff_id' => ['Staff member must belong to the same office as the service request.'],
            ]);
        }

        $serviceRequest->update([
            'assigned_staff_id' => $staffUser->id,
            'status' => $serviceRequest->status === ServiceRequestStatus::PENDING
                ? ServiceRequestStatus::UNDER_REVIEW
                : $serviceRequest->status,
            'reviewed_at' => $serviceRequest->reviewed_at ?? now(),
        ]);

        return $serviceRequest->fresh();
    }
}