<?php

namespace App\Support;

use App\Models\ServiceRequest;
use Carbon\CarbonInterface;

class ServiceRequestTimelineBuilder
{
    /**
     * @return list<array{
     *     key: string,
     *     label: string,
     *     status: string,
     *     occurred_at: null|string
     * }>
     */
    public function build(ServiceRequest $serviceRequest): array
    {
        $submittedAt = $serviceRequest->submitted_at ?? $serviceRequest->created_at;
        $reviewReached = $this->hasReachedReview($serviceRequest);
        $isCompleted = $serviceRequest->status === 'completed';

        return [
            $this->step(
                'submitted',
                'Request Submitted',
                $submittedAt !== null,
                $submittedAt
            ),
            $this->step(
                'reviewed',
                'Under Review',
                $reviewReached,
                $reviewReached ? $serviceRequest->reviewed_at : null
            ),
            $this->step(
                'completed',
                'Completed',
                $isCompleted,
                $isCompleted ? $serviceRequest->completed_at : null
            ),
        ];
    }

    private function hasReachedReview(ServiceRequest $serviceRequest): bool
    {
        if ($serviceRequest->reviewed_at !== null) {
            return true;
        }

        return ! in_array($serviceRequest->status, ['pending'], true);
    }

    /**
     * @return array{
     *     key: string,
     *     label: string,
     *     status: string,
     *     occurred_at: null|string
     * }
     */
    private function step(string $key, string $label, bool $isCompleted, mixed $occurredAt): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'status' => $isCompleted ? 'completed' : 'pending',
            'occurred_at' => $this->formatTimestamp($occurredAt),
        ];
    }

    private function formatTimestamp(mixed $value): ?string
    {
        if ($value instanceof CarbonInterface) {
            return $value->toISOString();
        }

        return null;
    }
}
