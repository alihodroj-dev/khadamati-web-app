<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceRequestCertificateTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_certificate_json_for_completed_request(): void
    {
        $citizen = User::factory()->create([
            'role' => User::ROLE_CITIZEN,
            'name' => 'Ali Hodroj',
        ]);

        $serviceRequest = $this->createCompletedRequest($citizen);

        $response = $this->actingAs($citizen)->getJson(
            "/api/my-requests/{$serviceRequest->id}/certificate"
        );

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.certificate.certificate_number', 'CERT-'.$serviceRequest->reference_number)
            ->assertJsonPath('data.certificate.request_reference', $serviceRequest->reference_number)
            ->assertJsonPath('data.certificate.service_name', 'Passport Renewal')
            ->assertJsonPath('data.certificate.citizen_name', 'Ali Hodroj')
            ->assertJsonPath('data.certificate.office_name', 'Beirut Office')
            ->assertJsonPath('data.certificate.status', 'valid')
            ->assertJsonStructure([
                'data' => [
                    'certificate' => ['issued_at'],
                ],
            ]);
    }

    public function test_rejects_non_completed_request(): void
    {
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $serviceRequest = $this->createCompletedRequest($citizen);
        $serviceRequest->update(['status' => 'under_review']);

        $this->actingAs($citizen)->getJson(
            "/api/my-requests/{$serviceRequest->id}/certificate"
        )->assertStatus(422);
    }

    public function test_forbidden_for_other_users(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $other = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $serviceRequest = $this->createCompletedRequest($owner);

        $this->actingAs($other)->getJson(
            "/api/my-requests/{$serviceRequest->id}/certificate"
        )->assertForbidden();
    }

    private function createCompletedRequest(User $citizen): ServiceRequest
    {
        $office = Office::query()->create([
            'name' => 'Beirut Office',
            'address' => 'Hamra',
            'is_active' => true,
        ]);

        $category = ServiceCategory::query()->create([
            'name' => 'Civil',
            'is_active' => true,
        ]);

        $service = Service::query()->create([
            'service_category_id' => $category->id,
            'office_id' => $office->id,
            'name' => 'Passport Renewal',
            'base_fee' => 10,
            'is_active' => true,
        ]);

        return ServiceRequest::query()->create([
            'user_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'reference_number' => 'KHR-20260517-ABC123',
            'tracking_token' => ServiceRequest::generateTrackingToken(),
            'status' => 'completed',
            'submitted_at' => Carbon::parse('2026-05-10T09:00:00Z'),
            'reviewed_at' => Carbon::parse('2026-05-12T10:00:00Z'),
            'completed_at' => Carbon::parse('2026-05-15T14:00:00Z'),
        ]);
    }
}
