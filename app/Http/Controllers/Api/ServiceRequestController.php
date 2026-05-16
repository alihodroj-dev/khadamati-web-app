<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceRequestResource;
use App\Models\Office;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceRequestController extends Controller
{
    use ApiResponse;

    private const CANCELLABLE_STATUSES = [
        'pending',
        'under_review',
        'requires_action',
    ];

    private const STATUSES = [
        'pending',
        'under_review',
        'requires_action',
        'approved',
        'rejected',
        'completed',
        'cancelled',
    ];

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'office_id' => ['nullable', 'integer', 'exists:offices,id'],
            'citizen_notes' => ['nullable', 'string'],
            'submitted_data' => ['nullable', 'array'],
        ]);

        $service = Service::where('id', $validated['service_id'])
            ->where('is_active', true)
            ->first();

        if (! $service) {
            return $this->errorResponse(
                'Selected service is not available.',
                null,
                404
            );
        }

        $officeId = $this->resolveOfficeIdForRequest(
            $service,
            $validated['office_id'] ?? null
        );

        if ($officeId === false) {
            $message = $service->office_id
                ? 'The selected office does not match this service.'
                : 'The selected office is not available.';

            return $this->errorResponse($message, null, 422);
        }

        $serviceRequest = ServiceRequest::create([
            'user_id' => $request->user()->id,
            'service_id' => $service->id,
            'office_id' => $officeId,
            'reference_number' => $this->generateReferenceNumber(),
            'tracking_token' => ServiceRequest::generateTrackingToken(),
            'status' => 'pending',
            'citizen_notes' => $validated['citizen_notes'] ?? null,
            'submitted_data' => $validated['submitted_data'] ?? null,
            'submitted_at' => now(),
        ]);

        $serviceRequest->load(['service.category', 'office']);

        return $this->successResponse(
            [
                'service_request' => new ServiceRequestResource($serviceRequest),
            ],
            'Service request submitted successfully',
            201
        );
    }

    public function myRequests(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', Rule::in(self::STATUSES)],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'category_id' => ['nullable', 'integer', 'exists:service_categories,id'],
            'from_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'to_date' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $query = ServiceRequest::query()
            ->where('user_id', $request->user()->id)
            ->with(['service.category', 'office']);

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (! empty($validated['service_id'])) {
            $query->where('service_id', $validated['service_id']);
        }

        if (! empty($validated['category_id'])) {
            $query->whereHas('service', function ($serviceQuery) use ($validated) {
                $serviceQuery->where('service_category_id', $validated['category_id']);
            });
        }

        if (! empty($validated['from_date'])) {
            $query->whereDate('submitted_at', '>=', $validated['from_date']);
        }

        if (! empty($validated['to_date'])) {
            $query->whereDate('submitted_at', '<=', $validated['to_date']);
        }

        if (! empty($validated['search'])) {
            $term = $validated['search'];
            $query->where(function ($searchQuery) use ($term) {
                $searchQuery
                    ->where('reference_number', 'like', '%'.$term.'%')
                    ->orWhereHas('service', function ($serviceQuery) use ($term) {
                        $serviceQuery->where('name', 'like', '%'.$term.'%');
                    });
            });
        }

        $requests = $query
            ->latest()
            ->get();

        return $this->successResponse(
            [
                'service_requests' => ServiceRequestResource::collection($requests),
            ],
            'Service requests retrieved successfully'
        );
    }

    public function show(Request $request, ServiceRequest $serviceRequest)
    {
        if ($serviceRequest->user_id !== $request->user()->id) {
            return $this->errorResponse(
                'You are not allowed to view this service request.',
                null,
                403
            );
        }

        $serviceRequest->load([
            'service.category',
            'office',
            'documents',
            'appointment',
            'payment',
            'feedback',
        ]);

        return $this->successResponse(
            [
                'service_request' => new ServiceRequestResource($serviceRequest),
            ],
            'Service request retrieved successfully'
        );
    }

    public function cancel(Request $request, ServiceRequest $serviceRequest)
    {
        if ($serviceRequest->user_id !== $request->user()->id) {
            return $this->errorResponse(
                'You are not allowed to cancel this service request.',
                null,
                403
            );
        }

        if (! in_array($serviceRequest->status, self::CANCELLABLE_STATUSES, true)) {
            return $this->errorResponse(
                'This service request cannot be cancelled.',
                null,
                422
            );
        }

        $serviceRequest->update(['status' => 'cancelled']);
        $serviceRequest->load(['service.category', 'office']);

        return $this->successResponse(
            new ServiceRequestResource($serviceRequest),
            'Service request cancelled successfully.'
        );
    }

    /**
     * @return int|null|false office id, null if none, false if invalid office
     */
    private function resolveOfficeIdForRequest(Service $service, ?int $requestedOfficeId): int|null|false
    {
        if ($service->office_id) {
            if ($requestedOfficeId !== null && (int) $requestedOfficeId !== (int) $service->office_id) {
                return false;
            }

            return (int) $service->office_id;
        }

        if ($requestedOfficeId === null) {
            return null;
        }

        $officeExists = Office::query()
            ->where('id', $requestedOfficeId)
            ->where('is_active', true)
            ->exists();

        return $officeExists ? (int) $requestedOfficeId : false;
    }

    private function generateReferenceNumber(): string
    {
        do {
            $reference = 'KHR-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        } while (ServiceRequest::where('reference_number', $reference)->exists());

        return $reference;
    }
}
