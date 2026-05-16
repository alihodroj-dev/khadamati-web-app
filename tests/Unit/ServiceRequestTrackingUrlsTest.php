<?php

namespace Tests\Unit;

use App\Models\ServiceRequest;
use App\Support\ServiceRequestTrackingUrls;
use Tests\TestCase;

class ServiceRequestTrackingUrlsTest extends TestCase
{
    public function test_builds_api_and_web_urls_from_app_url(): void
    {
        config(['app.url' => 'https://example.com']);
        \Illuminate\Support\Facades\URL::forceRootUrl('https://example.com');
        \Illuminate\Support\Facades\URL::forceScheme('https');

        $serviceRequest = new ServiceRequest([
            'tracking_token' => 'abc123token',
        ]);

        $urls = ServiceRequestTrackingUrls::for($serviceRequest);

        $this->assertSame('abc123token', $urls['tracking_token']);
        $this->assertSame('https://example.com/api/track/abc123token', $urls['tracking_api_url']);
        $this->assertSame('https://example.com/track/abc123token', $urls['tracking_web_url']);
    }

    public function test_returns_null_urls_when_token_missing(): void
    {
        $serviceRequest = new ServiceRequest([
            'tracking_token' => null,
        ]);

        $urls = ServiceRequestTrackingUrls::for($serviceRequest);

        $this->assertNull($urls['tracking_token']);
        $this->assertNull($urls['tracking_api_url']);
        $this->assertNull($urls['tracking_web_url']);
    }
}
