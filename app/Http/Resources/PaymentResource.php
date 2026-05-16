<?php

namespace App\Http\Resources;

use App\Support\PaymentNextActionBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_request_id' => $this->service_request_id,
            'appointment_id' => $this->appointment_id,
            'user_id' => $this->user_id,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'transaction_reference' => $this->transaction_reference,
            'payment_details' => $this->payment_details ?? [],
            'next_action' => PaymentNextActionBuilder::for($this->resource),
            'paid_at' => $this->paid_at?->toISOString(),
            'service_request' => $this->whenLoaded('serviceRequest', fn () => [
                'id' => $this->serviceRequest->id,
                'reference_number' => $this->serviceRequest->reference_number,
                'status' => $this->serviceRequest->status,
            ]),
            'appointment' => $this->whenLoaded('appointment', fn () => [
                'id' => $this->appointment->id,
                'appointment_date' => $this->appointment->appointment_date?->toDateString(),
                'appointment_time' => $this->appointment->appointment_time,
                'status' => $this->appointment->status,
            ]),
            'user' => $this->whenLoaded('user'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
