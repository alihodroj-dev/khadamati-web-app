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
            'service_request_status' => $this->serviceRequest?->status,
            'office_name' => $this->serviceRequest?->office?->name,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->status,
            'receipt_status' => 'valid',
            'paid_at' => $this->paid_at?->toISOString(),
            'issued_at' => $this->paid_at?->toISOString(),
            'citizen_name' => $this->citizenName(),
            ...($this->includeCitizenNationalId($request) ? [
                'citizen_national_id' => $this->user?->national_id,
            ] : []),
        ];
    }

    private function citizenName(): ?string
    {
        $user = $this->user;

        if ($user === null) {
            return null;
        }

        $name = trim($user->name ?? '');

        if ($name !== '') {
            return $name;
        }

        $parts = array_filter([
            $user->first_name ?? null,
            $user->last_name ?? null,
        ]);

        return $parts !== [] ? implode(' ', $parts) : null;
    }

    private function includeCitizenNationalId(Request $request): bool
    {
        $user = $request->user();

        if ($user === null) {
            return false;
        }

        return (int) $user->id === (int) $this->user_id
            && filled($this->user?->national_id);
    }
}
