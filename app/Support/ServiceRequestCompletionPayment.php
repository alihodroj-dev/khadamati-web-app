<?php

namespace App\Support;

use App\Models\Payment;
use App\Models\ServiceRequest;

class ServiceRequestCompletionPayment
{
    /**
     * Create a pending desk payment when staff/admin marks a request completed.
     * Skips creation when a payment already exists or the service has no fee.
     */
    public static function ensurePendingDeskPayment(ServiceRequest $serviceRequest): ?Payment
    {
        $serviceRequest->loadMissing('service', 'payment');

        if ($serviceRequest->payment !== null) {
            return $serviceRequest->payment;
        }

        $amount = (float) ($serviceRequest->service?->base_fee ?? 0);

        if ($amount <= 0) {
            return null;
        }

        return Payment::query()->create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => $serviceRequest->user_id,
            'amount' => $amount,
            'currency' => 'USD',
            'payment_method' => 'cash',
            'status' => 'pending',
            'transaction_reference' => uniqid('PAY-', true),
        ]);
    }
}
