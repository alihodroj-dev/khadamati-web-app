<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentReceiptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'receipt_number' => sprintf(
                'RCP-%s-%06d',
                $this->paid_at?->format('Ymd') ?? now()->format('Ymd'),
                $this->id
            ),
            'payment_id' => $this->id,
            'transaction_reference' => $this->transaction_reference,
            'request_reference_number' => $this->serviceRequest?->reference_number,
            'service_name' => $this->serviceRequest?->service?->name,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->status,
            'paid_at' => $this->paid_at?->toISOString(),
            'citizen_name' => $this->user?->name,
        ];
    }
}
