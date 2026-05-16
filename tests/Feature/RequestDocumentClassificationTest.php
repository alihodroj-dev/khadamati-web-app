<?php

namespace Tests\Feature;

use App\Models\RequestDocument;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequestDocumentClassificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_documents_ignores_staff_official_uploads(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $serviceRequest = $this->createServiceRequestFor($user, ['National ID copy']);

        RequestDocument::query()->create([
            'service_request_id' => $serviceRequest->id,
            'uploaded_by' => $user->id,
            'source' => RequestDocument::SOURCE_STAFF,
            'purpose' => RequestDocument::PURPOSE_CERTIFICATE,
            'document_type' => 'certificate',
            'file_name' => 'cert.pdf',
            'file_path' => 'request-documents/1/cert.pdf',
            'status' => 'approved',
        ]);

        $serviceRequest->load(['service', 'documents']);

        $this->assertCount(1, $serviceRequest->missingRequiredDocuments());
    }

    public function test_list_documents_groups_by_purpose(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $serviceRequest = $this->createServiceRequestFor($user, ['National ID copy']);

        RequestDocument::query()->create([
            'service_request_id' => $serviceRequest->id,
            'uploaded_by' => $user->id,
            'source' => RequestDocument::SOURCE_CITIZEN,
            'purpose' => RequestDocument::PURPOSE_REQUIREMENT,
            'document_type' => 'national_id_copy',
            'file_name' => 'id.jpg',
            'file_path' => 'request-documents/1/id.jpg',
            'status' => 'pending',
        ]);

        RequestDocument::query()->create([
            'service_request_id' => $serviceRequest->id,
            'uploaded_by' => $user->id,
            'source' => RequestDocument::SOURCE_STAFF,
            'purpose' => RequestDocument::PURPOSE_CERTIFICATE,
            'document_type' => 'certificate',
            'file_name' => 'cert.pdf',
            'file_path' => 'request-documents/1/cert.pdf',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($user)->getJson(
            "/api/my-requests/{$serviceRequest->id}/documents"
        );

        $response->assertOk()
            ->assertJsonCount(2, 'data.documents')
            ->assertJsonCount(1, 'data.requirement_documents')
            ->assertJsonCount(1, 'data.official_documents')
            ->assertJsonPath('data.official_documents.0.purpose', 'certificate');
    }

    private function createServiceRequestFor(User $user, array $requiredDocuments): ServiceRequest
    {
        $category = ServiceCategory::query()->create([
            'name' => 'Civil',
            'is_active' => true,
        ]);

        $service = Service::query()->create([
            'service_category_id' => $category->id,
            'name' => 'Residency',
            'base_fee' => 10,
            'required_documents' => $requiredDocuments,
            'is_active' => true,
        ]);

        return ServiceRequest::query()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'reference_number' => 'REQ-'.uniqid(),
            'tracking_token' => ServiceRequest::generateTrackingToken(),
            'status' => 'pending',
        ]);
    }
}
