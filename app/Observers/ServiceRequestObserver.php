<?php

namespace App\Observers;

use App\Models\ServiceRequest;
use App\Services\ServiceRequestQrCodeService;

class ServiceRequestObserver
{
    public function __construct(
        private readonly ServiceRequestQrCodeService $qrCodeService,
    ) {}

    public function created(ServiceRequest $serviceRequest): void
    {
        $this->qrCodeService->generateAndStore($serviceRequest);
    }
}
