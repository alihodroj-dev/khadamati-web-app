<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceRequestResource;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceRequestController extends Controller
{
    use ApiResponse;

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
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

        $serviceRequest = ServiceRequest::create([
            'user_id' => $request->user()->id,
            'service_id' => $service->id,
            'reference_number' => $this->generateReferenceNumber(),
            'status' => 'pending',
            'citizen_notes' => $validated['citizen_notes'] ?? null,
            'submitted_data' => $validated['submitted_data'] ?? null,
            'submitted_at' => now(),
        ]);

        $serviceRequest->load('service.category');

        return $this->successResponse(
            'Service request submitted successfully',
            [
                'service_request' => new ServiceRequestResource($serviceRequest),
            ],
            201
        );
    }

    public function myRequests(Request $request)
    {
        $query = ServiceRequest::query()
            ->where('user_id', $request->user()->id)
            ->with('service.category');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query
            ->latest()
            ->get();

        return $this->successResponse(
            'Service requests retrieved successfully',
            [
                'service_requests' => ServiceRequestResource::collection($requests),
            ]
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
            'documents',
            'appointment',
            'payment',
            'feedback',
        ]);

        return $this->successResponse(
            'Service request retrieved successfully',
            [
                'service_request' => new ServiceRequestResource($serviceRequest),
            ]
        );
    }

    private function generateReferenceNumber(): string
    {
        do {
            $reference = 'KHR-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        } while (ServiceRequest::where('reference_number', $reference)->exists());

        return $reference;
    }
}
