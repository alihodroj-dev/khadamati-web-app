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

class OfficeFeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_public_feedback_for_completed_requests_at_office(): void
    {
        $office = $this->createOffice();
        $otherOffice = $this->createOffice('Other Office');
        $citizen = User::factory()->create([
            'name' => 'Jane Hodroj',
            'email' => 'jane@secret.com',
            'phone' => '+96170000001',
            'national_id' => '123456789',
        ]);
        $service = $this->createService($office, 'Passport Renewal');

        $completedRequest = $this->createServiceRequest($citizen, $service, $office, 'completed');
        $this->createServiceRequest($citizen, $service, $office, 'pending');
        $this->createServiceRequest(
            $citizen,
            $service,
            $otherOffice,
            'completed'
        );

        Feedback::query()->create([
            'service_request_id' => $completedRequest->id,
            'user_id' => $citizen->id,
            'rating' => 5,
            'comment' => 'Very helpful staff.',
        ]);

        $response = $this->getJson("/api/offices/{$office->id}/feedback");

        $response->assertOk()
            ->assertJsonCount(1, 'data.feedback')
            ->assertJsonPath('data.feedback.0.rating', 5)
            ->assertJsonPath('data.feedback.0.comment', 'Very helpful staff.')
            ->assertJsonPath('data.feedback.0.citizen_name', 'Jane')
            ->assertJsonPath('data.feedback.0.service_name', 'Passport Renewal')
            ->assertJsonMissingPath('data.feedback.0.email')
            ->assertJsonMissingPath('data.feedback.0.phone')
            ->assertJsonMissingPath('data.feedback.0.user');
    }

    public function test_filters_feedback_by_rating(): void
    {
        $office = $this->createOffice();
        $citizen = User::factory()->create(['name' => 'Ali Hodroj']);
        $service = $this->createService($office, 'ID Card');

        $requestFive = $this->createServiceRequest($citizen, $service, $office, 'completed');
        $requestThree = $this->createServiceRequest($citizen, $service, $office, 'completed');

        Feedback::query()->create([
            'service_request_id' => $requestFive->id,
            'user_id' => $citizen->id,
            'rating' => 5,
            'comment' => 'Excellent',
        ]);

        Feedback::query()->create([
            'service_request_id' => $requestThree->id,
            'user_id' => $citizen->id,
            'rating' => 3,
            'comment' => 'Average',
        ]);

        $response = $this->getJson("/api/offices/{$office->id}/feedback?rating=5");

        $response->assertOk()
            ->assertJsonCount(1, 'data.feedback')
            ->assertJsonPath('data.feedback.0.rating', 5);
    }

    public function test_inactive_office_returns_not_found(): void
    {
        $office = Office::query()->create([
            'name' => 'Closed Office',
            'address' => 'Address',
            'is_active' => false,
        ]);

        $this->getJson("/api/offices/{$office->id}/feedback")
            ->assertNotFound();
    }

    private function createOffice(string $name = 'Beirut Office'): Office
    {
        return Office::query()->create([
            'name' => $name,
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
