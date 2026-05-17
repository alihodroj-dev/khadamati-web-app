<?php

namespace App\Support;

use App\Models\ServiceRequest;

class ServiceRequestStatusUpdater
{
    /**
     * @return array<string, mixed>
     */
    public static function buildUpdatePayload(
        string $status,
        ?string $staffNotes = null,
        ?string $rejectionReason = null
    ): array {
        $updateData = ['status' => $status];

        if ($staffNotes !== null) {
            $updateData['staff_notes'] = $staffNotes;
        }

        if ($status === ServiceRequestStatus::REJECTED) {
            $updateData['rejection_reason'] = $rejectionReason;
        }

        if (in_array($status, [
            ServiceRequestStatus::APPROVED,
            ServiceRequestStatus::UNDER_REVIEW,
            ServiceRequestStatus::REQUIRES_ACTION,
        ], true)) {
            $updateData['reviewed_at'] = now();
        }

        if ($status === ServiceRequestStatus::COMPLETED) {
            $updateData['reviewed_at'] = now();
            $updateData['completed_at'] = now();
        }

        if ($status === ServiceRequestStatus::REJECTED) {
            $updateData['reviewed_at'] = now();
        }

        return $updateData;
    }

    public static function apply(
        ServiceRequest $serviceRequest,
        string $status,
        ?string $staffNotes = null,
        ?string $rejectionReason = null,
        bool $createDeskPaymentOnCompletion = true
    ): ServiceRequest {
        $serviceRequest->update(
            self::buildUpdatePayload($status, $staffNotes, $rejectionReason)
        );

        if ($createDeskPaymentOnCompletion && $status === ServiceRequestStatus::COMPLETED) {
            ServiceRequestCompletionPayment::ensurePendingDeskPayment($serviceRequest);
        }

        return $serviceRequest->fresh();
    }
}
