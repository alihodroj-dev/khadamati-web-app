<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\Office;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficeDiscoveryStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_office_list_includes_discovery_stats_from_completed_requests(): void
    {
        $office = $this->createOffice();
        $citizen = User::factory()->create(['name' => 'Jane Doe']);
        $serviceA = $this->createService($office, 'Service A');
        $serviceB = $this->createService($office, 'Service B');

        $completedA = $this->createServiceRequest($citizen, $serviceA, $office, 'completed');
        $completedB = $this->createServiceRequest($citizen, $serviceB, $office, 'completed');
        $this->createServiceRequest($citizen, $serviceA, $office, 'pending');

        Feedback::query()->create([
            'service_request_id' => $completedA->id,
            'user_id' => $citizen->id,
            'rating' => 4,
            'comment' => 'Good',
        ]);

        Feedback::query()->create([
            'service_request_id' => $completedB->id,
            'user_id' => $citizen->id,
            'rating' => 5,
            'comment' => 'Great',
        ]);

        $response = $this->getJson('/api/offices');

        $response->assertOk()
            ->assertJsonPath('data.offices.0.services_count', 2)
            ->assertJsonPath('data.offices.0.ratings_count', 2)
            ->assertJsonPath('data.offices.0.average_rating', 4.5);
    }

    public function test_office_show_includes_discovery_stats(): void
    {
        $office = $this->createOffice();
        $citizen = User::factory()->create();
        $service = $this->createService($office, 'Service A');
        $completed = $this->createServiceRequest($citizen, $service, $office, 'completed');

        Feedback::query()->create([
            'service_request_id' => $completed->id,
            'user_id' => $citizen->id,
            'rating' => 5,
        ]);

        $response = $this->getJson("/api/offices/{$office->id}");

        $response->assertOk()
            ->assertJsonPath('data.office.services_count', 1)
            ->assertJsonPath('data.office.ratings_count', 1)
            ->assertJsonPath('data.office.average_rating', 5);
    }

    public function test_office_without_completed_feedback_returns_zero_counts_and_null_average(): void
    {
        $office = $this->createOffice();

        $response = $this->getJson("/api/offices/{$office->id}");

        $response->assertOk()
            ->assertJsonPath('data.office.services_count', 0)
            ->assertJsonPath('data.office.ratings_count', 0)
            ->assertJsonPath('data.office.average_rating', null);
    }

    private function createOffice(): Office
    {
        return Office::query()->create([
            'name' => 'Beirut Office',
            'address' => 'Hamra',
            'latitude' => 33.9,
            'longitude' => 35.5,
            'is_active' => true,
        ]);
    }

    private function createService(Office $office, string $name): Service
    {
        $category = ServiceCategory::query()->create([
            'name' => 'Civil',
            'is_active' => true,
        ]);

        return Service::query()->create([
            'service_category_id' => $category->id,
            'office_id' => $office->id,
            'name' => $name,
            'base_fee' => 10,
            'is_active' => true,
        ]);
    }

    private function createServiceRequest(
        User $user,
        Service $service,
        Office $office,
        string $status
    ): ServiceRequest {
        return ServiceRequest::query()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'reference_number' => 'REQ-'.uniqid(),
            'tracking_token' => ServiceRequest::generateTrackingToken(),
            'status' => $status,
        ]);
    }
}
