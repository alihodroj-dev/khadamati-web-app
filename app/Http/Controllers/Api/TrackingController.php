<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicServiceRequestTrackingResource;
use App\Models\ServiceRequest;
use App\Traits\ApiResponse;

class TrackingController extends Controller
{
    use ApiResponse;

    public function show(string $trackingToken)
    {
        $serviceRequest = ServiceRequest::query()
            ->with('service')
            ->where('tracking_token', $trackingToken)
            ->first();

        if (! $serviceRequest) {
            return $this->errorResponse(
                'Service request not found.',
                null,
                404
            );
        }

        return $this->successResponse(
            new PublicServiceRequestTrackingResource($serviceRequest),
            'Service request tracking retrieved successfully.'
        );
    }
}
