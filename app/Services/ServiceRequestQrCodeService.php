<?php

namespace App\Services;

use App\Models\ServiceRequest;
use App\Support\ServiceRequestTrackingUrls;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

class ServiceRequestQrCodeService
{
    public function ensureStored(ServiceRequest $serviceRequest): ?string
    {
        if (! is_string($serviceRequest->tracking_token) || $serviceRequest->tracking_token === '') {
            return null;
        }

        if (
            is_string($serviceRequest->qr_code_path)
            && $serviceRequest->qr_code_path !== ''
            && Storage::disk('public')->exists($serviceRequest->qr_code_path)
        ) {
            return $serviceRequest->qr_code_path;
        }

        return $this->generateAndStore($serviceRequest);
    }

    public function generateAndStore(ServiceRequest $serviceRequest): ?string
    {
        $trackingWebUrl = ServiceRequestTrackingUrls::for($serviceRequest)['tracking_web_url'];

        if ($trackingWebUrl === null) {
            return null;
        }

        if ($serviceRequest->id === null) {
            return null;
        }

        $path = 'service-requests/'.$serviceRequest->id.'/tracking-qr.png';

        Storage::disk('public')->put($path, $this->generatePng($trackingWebUrl));

        if ($serviceRequest->qr_code_path !== $path) {
            $serviceRequest->forceFill(['qr_code_path' => $path])->saveQuietly();
        }

        return $path;
    }

    public function publicUrl(?string $qrCodePath): ?string
    {
        if ($qrCodePath === null || $qrCodePath === '') {
            return null;
        }

        if (! Storage::disk('public')->exists($qrCodePath)) {
            return null;
        }

        return Storage::disk('public')->url($qrCodePath);
    }

    private function generatePng(string $content): string
    {
        $writer = new PngWriter;

        $result = $writer->write(
            QrCode::create($content)->setSize(280)->setMargin(10)
        );

        return $result->getString();
    }
}
