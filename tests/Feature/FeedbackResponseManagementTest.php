<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\FeedbackResponse;
use App\Models\Office;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FeedbackResponseManagementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function staff_can_list_completed_feedback_for_their_office(): void
    {
        $officeA = $this->createOffice('Office A');
        $officeB = $this->createOffice('Office B');

        $staff = $this->createStaff($officeA);
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);

        $completedFeedback = $this->createFeedback($citizen, $officeA, 'completed', 'Great service');
        $this->createFeedback($citizen, $officeA, 'pending', 'Should not appear');
        $this->createFeedback($citizen, $officeB, 'completed', 'Other office');

        $this->actingAs($staff);

        $this->getJson('/api/staff/feedback')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $completedFeedback->id)
            ->assertJsonPath('data.0.comment', 'Great service');
    }

    #[Test]
    public function staff_can_respond_to_office_feedback_with_public_or_private_visibility(): void
    {
        $office = $this->createOffice('Office A');
        $staff = $this->createStaff($office);
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $feedback = $this->createFeedback($citizen, $office, 'completed', 'Thanks');

        $this->actingAs($staff);

        $this->postJson("/api/staff/feedback/{$feedback->id}/responses", [
            'visibility' => FeedbackResponse::VISIBILITY_PUBLIC,
            'message' => 'We appreciate your feedback.',
        ])
            ->assertCreated()
            ->assertJsonPath('data.visibility', FeedbackResponse::VISIBILITY_PUBLIC)
            ->assertJsonPath('data.message', 'We appreciate your feedback.');

        $this->postJson("/api/staff/feedback/{$feedback->id}/responses", [
            'visibility' => FeedbackResponse::VISIBILITY_PRIVATE,
            'message' => 'Internal follow-up note.',
        ])->assertCreated();

        $this->assertDatabaseCount('feedback_responses', 2);
    }

    #[Test]
    public function staff_cannot_respond_to_feedback_from_another_office(): void
    {
        $officeA = $this->createOffice('Office A');
        $officeB = $this->createOffice('Office B');
        $staff = $this->createStaff($officeA);
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $feedback = $this->createFeedback($citizen, $officeB, 'completed');

        $this->actingAs($staff);

        $this->postJson("/api/staff/feedback/{$feedback->id}/responses", [
            'visibility' => FeedbackResponse::VISIBILITY_PUBLIC,
            'message' => 'Not allowed',
        ])->assertForbidden();
    }

    #[Test]
    public function admin_can_respond_to_any_feedback(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $office = $this->createOffice('Office A');
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $feedback = $this->createFeedback($citizen, $office, 'completed');

        $this->actingAs($admin);

        $this->postJson("/api/staff/feedback/{$feedback->id}/responses", [
            'visibility' => FeedbackResponse::VISIBILITY_PUBLIC,
            'message' => 'Admin reply',
        ])->assertCreated();
    }

    #[Test]
    public function staff_can_update_and_delete_own_responses(): void
    {
        $office = $this->createOffice('Office A');
        $staff = $this->createStaff($office);
        $otherStaff = $this->createStaff($office, 'other@office.test');
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $feedback = $this->createFeedback($citizen, $office, 'completed');

        $ownResponse = FeedbackResponse::query()->create([
            'feedback_id' => $feedback->id,
            'responder_id' => $staff->id,
            'visibility' => FeedbackResponse::VISIBILITY_PUBLIC,
            'message' => 'Original',
        ]);

        $otherResponse = FeedbackResponse::query()->create([
            'feedback_id' => $feedback->id,
            'responder_id' => $otherStaff->id,
            'visibility' => FeedbackResponse::VISIBILITY_PUBLIC,
            'message' => 'Other staff',
        ]);

        $this->actingAs($staff);

        $this->patchJson("/api/staff/feedback-responses/{$ownResponse->id}", [
            'message' => 'Updated message',
        ])
            ->assertOk()
            ->assertJsonPath('data.message', 'Updated message');

        $this->patchJson("/api/staff/feedback-responses/{$otherResponse->id}", [
            'message' => 'Hijack',
        ])->assertForbidden();

        $this->deleteJson("/api/staff/feedback-responses/{$ownResponse->id}")
            ->assertOk();

        $this->assertDatabaseMissing('feedback_responses', ['id' => $ownResponse->id]);
    }

    #[Test]
    public function admin_can_delete_any_feedback_response(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $office = $this->createOffice('Office A');
        $staff = $this->createStaff($office);
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $feedback = $this->createFeedback($citizen, $office, 'completed');

        $response = FeedbackResponse::query()->create([
            'feedback_id' => $feedback->id,
            'responder_id' => $staff->id,
            'visibility' => FeedbackResponse::VISIBILITY_PRIVATE,
            'message' => 'Remove me',
        ]);

        $this->actingAs($admin);

        $this->deleteJson("/api/staff/feedback-responses/{$response->id}")
            ->assertOk();

        $this->assertDatabaseMissing('feedback_responses', ['id' => $response->id]);
    }

    #[Test]
    public function public_office_feedback_includes_only_public_responses(): void
    {
        $office = $this->createOffice('Office A');
        $staff = $this->createStaff($office);
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN, 'name' => 'Jane Hodroj']);
        $feedback = $this->createFeedback($citizen, $office, 'completed', 'Nice office');

        FeedbackResponse::query()->create([
            'feedback_id' => $feedback->id,
            'responder_id' => $staff->id,
            'visibility' => FeedbackResponse::VISIBILITY_PUBLIC,
            'message' => 'Thank you for visiting.',
        ]);

        FeedbackResponse::query()->create([
            'feedback_id' => $feedback->id,
            'responder_id' => $staff->id,
            'visibility' => FeedbackResponse::VISIBILITY_PRIVATE,
            'message' => 'Internal only',
        ]);

        $this->getJson("/api/offices/{$office->id}/feedback")
            ->assertOk()
            ->assertJsonCount(1, 'data.feedback')
            ->assertJsonCount(1, 'data.feedback.0.responses')
            ->assertJsonPath('data.feedback.0.responses.0.message', 'Thank you for visiting.')
            ->assertJsonMissingPath('data.feedback.0.responses.1');
    }

    #[Test]
    public function public_service_feedback_includes_only_public_responses(): void
    {
        $office = $this->createOffice('Office A');
        $staff = $this->createStaff($office);
        $service = $this->createService($office, 'Permit');
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $request = $this->createServiceRequest($citizen, $service, $office, 'completed');

        $feedback = Feedback::query()->create([
            'service_request_id' => $request->id,
            'user_id' => $citizen->id,
            'rating' => 4,
            'comment' => 'Good service',
        ]);

        FeedbackResponse::query()->create([
            'feedback_id' => $feedback->id,
            'responder_id' => $staff->id,
            'visibility' => FeedbackResponse::VISIBILITY_PUBLIC,
            'message' => 'Glad we could help.',
        ]);

        FeedbackResponse::query()->create([
            'feedback_id' => $feedback->id,
            'responder_id' => $staff->id,
            'visibility' => FeedbackResponse::VISIBILITY_PRIVATE,
            'message' => 'Hidden',
        ]);

        $this->getJson("/api/services/{$service->id}/feedback")
            ->assertOk()
            ->assertJsonCount(1, 'data.feedback.0.responses')
            ->assertJsonPath('data.feedback.0.responses.0.message', 'Glad we could help.');
    }

    #[Test]
    public function citizen_feedback_detail_includes_only_public_responses(): void
    {
        $office = $this->createOffice('Office A');
        $staff = $this->createStaff($office);
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $feedback = $this->createFeedback($citizen, $office, 'completed');

        FeedbackResponse::query()->create([
            'feedback_id' => $feedback->id,
            'responder_id' => $staff->id,
            'visibility' => FeedbackResponse::VISIBILITY_PUBLIC,
            'message' => 'Public reply',
        ]);

        FeedbackResponse::query()->create([
            'feedback_id' => $feedback->id,
            'responder_id' => $staff->id,
            'visibility' => FeedbackResponse::VISIBILITY_PRIVATE,
            'message' => 'Private reply',
        ]);

        $this->actingAs($citizen);

        $this->getJson("/api/feedback/{$feedback->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data.responses')
            ->assertJsonPath('data.responses.0.message', 'Public reply');
    }

    #[Test]
    public function staff_feedback_list_includes_private_responses(): void
    {
        $office = $this->createOffice('Office A');
        $staff = $this->createStaff($office);
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $feedback = $this->createFeedback($citizen, $office, 'completed');

        FeedbackResponse::query()->create([
            'feedback_id' => $feedback->id,
            'responder_id' => $staff->id,
            'visibility' => FeedbackResponse::VISIBILITY_PRIVATE,
            'message' => 'Staff-only note',
        ]);

        $this->actingAs($staff);

        $this->getJson('/api/staff/feedback')
            ->assertOk()
            ->assertJsonCount(1, 'data.0.responses')
            ->assertJsonPath('data.0.responses.0.visibility', FeedbackResponse::VISIBILITY_PRIVATE);
    }

    #[Test]
    public function citizen_cannot_access_staff_feedback_routes(): void
    {
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);

        $this->actingAs($citizen);

        $this->getJson('/api/staff/feedback')->assertForbidden();
    }

    private function createOffice(string $name): Office
    {
        return Office::query()->create([
            'name' => $name,
            'address' => 'Main Street',
            'is_active' => true,
        ]);
    }

    private function createStaff(Office $office, string $email = 'staff@office.test'): User
    {
        return User::factory()->create([
            'role' => User::ROLE_STAFF,
            'office_id' => $office->id,
            'email' => $email,
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
        User $citizen,
        Service $service,
        Office $office,
        string $status
    ): ServiceRequest {
        return ServiceRequest::query()->create([
            'user_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'reference_number' => 'KHR-'.uniqid(),
            'tracking_token' => ServiceRequest::generateTrackingToken(),
            'status' => $status,
        ]);
    }

    private function createFeedback(
        User $citizen,
        Office $office,
        string $status,
        ?string $comment = 'Feedback comment'
    ): Feedback {
        $service = $this->createService($office, 'Service '.uniqid());
        $request = $this->createServiceRequest($citizen, $service, $office, $status);

        return Feedback::query()->create([
            'service_request_id' => $request->id,
            'user_id' => $citizen->id,
            'rating' => 5,
            'comment' => $comment,
        ]);
    }
}
