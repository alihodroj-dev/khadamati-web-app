<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\RequestDocument;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StaffRequestDocumentUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_upload_uses_valid_status_and_official_metadata(): void
    {
        Storage::fake('public');

        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $serviceRequest = $this->createAssignedServiceRequest($staff, $citizen);

        $response = $this->actingAs($staff)->post(
            "/staff/requests/{$serviceRequest->id}/upload",
            [
                'document_type' => 'certificate',
                'document' => UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf'),
            ]
        );

        $response->assertRedirect();

        $document = RequestDocument::query()->sole();

        $this->assertSame('approved', $document->status);
        $this->assertSame(RequestDocument::SOURCE_STAFF, $document->source);
        $this->assertSame(RequestDocument::PURPOSE_CERTIFICATE, $document->purpose);
        $this->assertSame('certificate', $document->document_type);
    }

    public function test_staff_upload_does_not_satisfy_missing_citizen_requirements(): void
    {
        Storage::fake('public');

        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $serviceRequest = $this->createAssignedServiceRequest($staff, $citizen);

        $this->actingAs($staff)->post(
            "/staff/requests/{$serviceRequest->id}/upload",
            [
                'document_type' => 'certificate',
                'document' => UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf'),
            ]
        );

        $serviceRequest->load(['service', 'documents']);

        $this->assertCount(1, $serviceRequest->missingRequiredDocuments());
    }

    private function createAssignedServiceRequest(User $staff, User $citizen): ServiceRequest
    {
        $office = Office::query()->create([
            'name' => 'Main Office',
            'address' => 'Center',
            'is_active' => true,
        ]);

        $staff->update(['office_id' => $office->id]);

        $category = ServiceCategory::query()->create([
            'name' => 'Civil',
            'is_active' => true,
        ]);

        $service = Service::query()->create([
            'service_category_id' => $category->id,
            'office_id' => $office->id,
            'name' => 'Residency',
            'base_fee' => 10,
            'required_documents' => ['National ID copy'],
            'is_active' => true,
        ]);

        return ServiceRequest::query()->create([
            'user_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'assigned_staff_id' => $staff->id,
            'reference_number' => 'REQ-'.uniqid(),
            'tracking_token' => ServiceRequest::generateTrackingToken(),
            'status' => 'pending',
        ]);
    }
}
