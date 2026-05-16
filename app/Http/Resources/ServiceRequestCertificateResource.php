<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceRequestCertificateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'certificate_number' => $this->resource['certificate_number'],
            'request_reference' => $this->resource['request_reference'],
            'service_name' => $this->resource['service_name'],
            'citizen_name' => $this->resource['citizen_name'],
            'office_name' => $this->resource['office_name'],
            'issued_at' => $this->resource['issued_at'],
            'status' => $this->resource['status'],
        ];
    }
}
