<?php

namespace Tests\Feature;

use App\Http\Resources\ServiceRequestResource;
use App\Models\Office;
use App\Models\Service;
use App\Models\User;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class PublicTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_track_endpoint_returns_public_payload(): void
    {
        $serviceRequest = $this->createTrackedRequest(
            trackingToken: 'track-token-123',
            referenceNumber: 'KHR-TEST-001',
            status: 'under_review',
        );

        $response = $this->getJson('/api/track/track-token-123');

        $response->assertOk()
            ->assertJsonPath('data.reference_number', 'KHR-TEST-001')
            ->assertJsonPath('data.status', 'under_review');
    }

    public function test_web_track_page_is_public(): void
    {
        $this->createTrackedRequest(
            trackingToken: 'web-track-token',
            referenceNumber: 'KHR-TEST-002',
            serviceName: 'Birth Certificate',
        );

        $this->get('/track/web-track-token')
            ->assertOk()
            ->assertSee('KHR-TEST-002')
            ->assertSee('Birth Certificate');
    }

    public function test_service_request_resource_includes_tracking_urls(): void
    {
        config(['app.url' => 'https://example.com']);
        \Illuminate\Support\Facades\URL::forceRootUrl('https://example.com');
        \Illuminate\Support\Facades\URL::forceScheme('https');

        $serviceRequest = new ServiceRequest([
            'tracking_token' => 'qr-token',
            'reference_number' => 'KHR-QR-001',
            'status' => 'pending',
        ]);

        $payload = (new ServiceRequestResource($serviceRequest))->resolve();

        $this->assertSame('https://example.com/api/track/qr-token', $payload['tracking_api_url']);
        $this->assertSame('https://example.com/track/qr-token', $payload['tracking_web_url']);
        $this->assertArrayNotHasKey('tracking_url', $payload);
        $this->assertCount(3, $payload['timeline']);
        $this->assertSame('submitted', $payload['timeline'][0]['key']);
    }

    public function test_citizen_cannot_see_staff_notes_in_service_request_resource(): void
    {
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $office = Office::query()->create([
            'name' => 'Main Office',
            'address' => 'Center',
            'is_active' => true,
        ]);
        $staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'office_id' => $office->id,
        ]);

        $serviceRequest = $this->createTrackedRequest(
            trackingToken: 'citizen-token',
            referenceNumber: 'KHR-CIT-001',
            officeId: $office->id,
        );
        $serviceRequest->update([
            'assigned_staff_id' => $staff->id,
            'staff_notes' => 'Internal staff note',
        ]);

        $citizenRequest = Request::create('/api/my-requests/1', 'GET');
        $citizenRequest->setUserResolver(fn () => $citizen);

        $payload = (new ServiceRequestResource($serviceRequest))
            ->toArray($citizenRequest);

        $this->assertArrayNotHasKey('staff_notes', $payload);

        $staffRequest = Request::create('/api/my-requests/1', 'GET');
        $staffRequest->setUserResolver(fn () => $staff);

        $staffPayload = (new ServiceRequestResource($serviceRequest))
            ->toArray($staffRequest);

        $this->assertSame('Internal staff note', $staffPayload['staff_notes']);
    }

    protected function createTrackedRequest(
        string $trackingToken,
        string $referenceNumber,
        string $status = 'pending',
        string $serviceName = 'Test Service',
        ?int $officeId = null,
    ): ServiceRequest {
        $category = ServiceCategory::create([
            'name' => 'Test Category',
            'is_active' => true,
        ]);

        $service = Service::create([
            'service_category_id' => $category->id,
            'name' => $serviceName,
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        return ServiceRequest::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'office_id' => $officeId,
            'reference_number' => $referenceNumber,
            'tracking_token' => $trackingToken,
            'status' => $status,
            'submitted_at' => now(),
        ]);
    }
}
