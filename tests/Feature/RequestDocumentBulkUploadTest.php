<?php

namespace Tests\Feature;

use App\Models\RequestDocument;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RequestDocumentBulkUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_upload_creates_multiple_documents(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $serviceRequest = $this->createServiceRequestFor($user, [
            'National ID copy',
            'Proof of address',
        ]);

        $response = $this->actingAs($user)->post(
            "/api/my-requests/{$serviceRequest->id}/documents/bulk",
            [
                'documents' => [
                    [
                        'document_type' => 'national_id_copy',
                        'file' => UploadedFile::fake()->image('id.jpg'),
                    ],
                    [
                        'document_type' => 'Proof of address',
                        'file' => UploadedFile::fake()->create('address.pdf', 100, 'application/pdf'),
                    ],
                ],
            ]
        );

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data.documents')
            ->assertJsonPath('data.documents.0.document_type', 'national_id_copy')
            ->assertJsonPath('data.documents.0.source', 'citizen')
            ->assertJsonPath('data.documents.0.purpose', 'requirement')
            ->assertJsonPath('data.documents.1.document_type', 'proof_of_address')
            ->assertJsonPath('data.documents.1.purpose', 'requirement');

        $this->assertSame(2, RequestDocument::query()->where('service_request_id', $serviceRequest->id)->count());
    }

    public function test_bulk_upload_rejects_when_request_is_completed(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $serviceRequest = $this->createServiceRequestFor($user, ['National ID copy'], 'completed');

        $response = $this->actingAs($user)->post(
            "/api/my-requests/{$serviceRequest->id}/documents/bulk",
            [
                'documents' => [
                    [
                        'document_type' => 'national_id_copy',
                        'file' => UploadedFile::fake()->image('id.jpg'),
                    ],
                ],
            ]
        );

        $response->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertSame(0, RequestDocument::count());
    }

    public function test_bulk_upload_forbidden_for_other_users(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $other = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $serviceRequest = $this->createServiceRequestFor($owner, ['National ID copy']);

        $response = $this->actingAs($other)->post(
            "/api/my-requests/{$serviceRequest->id}/documents/bulk",
            [
                'documents' => [
                    [
                        'document_type' => 'national_id_copy',
                        'file' => UploadedFile::fake()->image('id.jpg'),
                    ],
                ],
            ]
        );

        $response->assertForbidden();
    }

    /**
     * @param  list<string>  $requiredDocuments
     */
    private function createServiceRequestFor(
        User $user,
        array $requiredDocuments,
        string $status = 'pending'
    ): ServiceRequest {
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
            'status' => $status,
        ]);
    }
}
