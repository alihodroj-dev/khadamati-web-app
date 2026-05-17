<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\DocumentUploadedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentUploadNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_citizen_upload_notifies_assigned_staff(): void
    {
        Notification::fake();
        Storage::fake('public');

        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $serviceRequest = $this->createServiceRequest($citizen, $staff);

        $this->actingAs($citizen)->post(
            "/api/my-requests/{$serviceRequest->id}/documents",
            [
                'document_type' => 'national_id_copy',
                'document' => UploadedFile::fake()->image('id.jpg'),
            ]
        )->assertCreated();

        Notification::assertSentTo(
            $staff,
            DocumentUploadedNotification::class,
            fn (DocumentUploadedNotification $notification) => $notification->via($staff) === ['database']
        );
    }

    public function test_citizen_upload_does_not_notify_when_no_staff_assigned(): void
    {
        Notification::fake();
        Storage::fake('public');

        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $serviceRequest = $this->createServiceRequest($citizen, null);

        $this->actingAs($citizen)->post(
            "/api/my-requests/{$serviceRequest->id}/documents",
            [
                'document_type' => 'national_id_copy',
                'document' => UploadedFile::fake()->image('id.jpg'),
            ]
        )->assertCreated();

        Notification::assertNothingSent();
    }

    public function test_staff_official_upload_notifies_citizen(): void
    {
        Notification::fake();
        Storage::fake('public');

        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $serviceRequest = $this->createServiceRequest($citizen, $staff);

        $this->actingAs($staff)->post(
            route('staff.requests.uploadDocument', $serviceRequest->id),
            [
                'document_type' => 'certificate',
                'document' => UploadedFile::fake()->create('cert.pdf', 100, 'application/pdf'),
            ]
        )->assertRedirect();

        Notification::assertSentTo($citizen, DocumentUploadedNotification::class);
    }

    private function createServiceRequest(User $citizen, ?User $staff): ServiceRequest
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
            'required_documents' => ['National ID copy'],
            'is_active' => true,
        ]);

        return ServiceRequest::query()->create([
            'user_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'assigned_staff_id' => $staff?->id,
            'reference_number' => 'REQ-'.uniqid(),
            'tracking_token' => ServiceRequest::generateTrackingToken(),
            'status' => 'under_review',
        ]);
    }
}
