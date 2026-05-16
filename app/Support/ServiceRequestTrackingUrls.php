<?php

namespace App\Support;

use App\Models\ServiceRequest;

class ServiceRequestTrackingUrls
{
    /**
     * @return array{
     *     tracking_token: null|string,
     *     tracking_api_url: null|string,
     *     tracking_web_url: null|string
     * }
     */
    public static function for(ServiceRequest $serviceRequest): array
    {
        $token = $serviceRequest->tracking_token;

        if (! is_string($token) || $token === '') {
            return [
                'tracking_token' => null,
                'tracking_api_url' => null,
                'tracking_web_url' => null,
            ];
        }

        return [
            'tracking_token' => $token,
            'tracking_api_url' => url('/api/track/'.$token),
            'tracking_web_url' => url('/track/'.$token),
        ];
    }
}
