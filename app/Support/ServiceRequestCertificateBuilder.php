<?php

namespace App\Support;

use App\Models\ServiceRequest;
use Carbon\CarbonInterface;

class ServiceRequestCertificateBuilder
{
    /**
     * @return array{
     *     certificate_number: string,
     *     request_reference: string,
     *     service_name: string,
     *     citizen_name: string,
     *     office_name: string|null,
     *     issued_at: string,
     *     status: string
     * }
     */
    public function build(ServiceRequest $serviceRequest): array
    {
        $serviceRequest->loadMissing(['user', 'service', 'office']);

        $issuedAt = $serviceRequest->completed_at ?? $serviceRequest->updated_at ?? now();

        return [
            'certificate_number' => $this->certificateNumber($serviceRequest),
            'request_reference' => $serviceRequest->reference_number,
            'service_name' => (string) ($serviceRequest->service?->name ?? 'Service'),
            'citizen_name' => $this->citizenName($serviceRequest),
            'office_name' => $serviceRequest->office?->name,
            'issued_at' => $this->formatTimestamp($issuedAt),
            'status' => 'valid',
        ];
    }

    private function certificateNumber(ServiceRequest $serviceRequest): string
    {
        return 'CERT-'.$serviceRequest->reference_number;
    }

    private function citizenName(ServiceRequest $serviceRequest): string
    {
        $user = $serviceRequest->user;

        if ($user === null) {
            return 'Citizen';
        }

        $name = trim($user->name ?? '');

        if ($name !== '') {
            return $name;
        }

        $parts = array_filter([
            $user->first_name ?? null,
            $user->last_name ?? null,
        ]);

        return $parts !== [] ? implode(' ', $parts) : 'Citizen';
    }

    private function formatTimestamp(mixed $value): string
    {
        if ($value instanceof CarbonInterface) {
            return $value->toISOString();
        }

        return now()->toISOString();
    }
}
