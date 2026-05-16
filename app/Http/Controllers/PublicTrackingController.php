<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\View\View;

class PublicTrackingController extends Controller
{
    public function show(string $trackingToken): View
    {
        $serviceRequest = ServiceRequest::query()
            ->with('service')
            ->where('tracking_token', $trackingToken)
            ->firstOrFail();

        return view('tracking.show', [
            'serviceRequest' => $serviceRequest,
        ]);
    }
}
