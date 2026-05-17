<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StaffServiceManagementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function staff_can_crud_services_for_their_office_only(): void
    {
        $officeA = $this->createOffice('Office A');
        $officeB = $this->createOffice('Office B');
        $category = $this->createCategory();

        $staffA = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'office_id' => $officeA->id,
            'is_active' => true,
        ]);

        $serviceAtB = Service::query()->create([
            'service_category_id' => $category->id,
            'office_id' => $officeB->id,
            'name' => 'Office B Service',
            'base_fee' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($staffA);

        $this->postJson('/api/staff/services', [
            'service_category_id' => $category->id,
            'name' => 'Office A Permit',
            'description' => 'Local permit',
            'base_fee' => 25.5,
            'estimated_processing_days' => 7,
            'required_documents' => ['national_id', 'proof_of_address'],
            'requires_appointment' => true,
            'is_active' => true,
        ])
            ->assertCreated()
            ->assertJsonPath('data.office_id', $officeA->id)
            ->assertJsonPath('data.base_fee', 25.5)
            ->assertJsonPath('data.estimated_processing_days', 7);

        $service = Service::query()->where('name', 'Office A Permit')->first();
        $this->assertNotNull($service);
        $this->assertCount(2, $service->required_documents);

        $this->getJson('/api/staff/services')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson("/api/staff/services/{$service->id}")
            ->assertOk();

        $this->patchJson("/api/staff/services/{$service->id}", [
            'base_fee' => 30,
            'is_active' => false,
        ])
            ->assertOk()
            ->assertJsonPath('data.base_fee', 30)
            ->assertJsonPath('data.is_active', false);

        $this->getJson("/api/staff/services/{$serviceAtB->id}")->assertForbidden();
        $this->patchJson("/api/staff/services/{$serviceAtB->id}", ['name' => 'Hacked'])->assertForbidden();
        $this->deleteJson("/api/staff/services/{$serviceAtB->id}")->assertForbidden();

        $this->deleteJson("/api/staff/services/{$service->id}")->assertOk();
    }

    #[Test]
    public function staff_can_view_global_categories_but_not_create_them(): void
    {
        $office = $this->createOffice('Office A');
        $staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'office_id' => $office->id,
            'is_active' => true,
        ]);

        ServiceCategory::query()->create([
            'name' => 'Civil Services',
            'is_active' => true,
        ]);

        $this->actingAs($staff);

        $this->getJson('/api/staff/service-categories')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->postJson('/api/admin/service-categories', [
            'name' => 'New Category',
        ])->assertForbidden();
    }

    #[Test]
    public function staff_cannot_modify_global_services(): void
    {
        $office = $this->createOffice('Office A');
        $category = $this->createCategory();

        $staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'office_id' => $office->id,
            'is_active' => true,
        ]);

        $globalService = Service::query()->create([
            'service_category_id' => $category->id,
            'office_id' => null,
            'name' => 'Global Service',
            'base_fee' => 5,
            'is_active' => true,
        ]);

        $this->actingAs($staff);

        $this->getJson("/api/staff/services/{$globalService->id}")->assertForbidden();
        $this->patchJson("/api/staff/services/{$globalService->id}", ['name' => 'Changed'])->assertForbidden();
    }

    #[Test]
    public function admin_can_create_global_and_office_specific_services(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $office = $this->createOffice('Central');
        $category = $this->createCategory();

        $this->actingAs($admin);

        $this->postJson('/api/admin/services', [
            'service_category_id' => $category->id,
            'office_id' => null,
            'name' => 'Global Fee',
            'base_fee' => 15,
            'is_active' => true,
        ])->assertCreated()
            ->assertJsonPath('data.office_id', null);

        $this->postJson('/api/admin/services', [
            'service_category_id' => $category->id,
            'office_id' => $office->id,
            'name' => 'Office Fee',
            'base_fee' => 20,
            'estimated_processing_days' => 3,
            'required_documents' => ['national_id'],
            'is_active' => true,
        ])->assertCreated()
            ->assertJsonPath('data.office_id', $office->id);
    }

    #[Test]
    public function staff_cannot_delete_service_with_requests(): void
    {
        $office = $this->createOffice('Office A');
        $category = $this->createCategory();
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);

        $staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'office_id' => $office->id,
            'is_active' => true,
        ]);

        $service = Service::query()->create([
            'service_category_id' => $category->id,
            'office_id' => $office->id,
            'name' => 'Busy Service',
            'base_fee' => 10,
            'is_active' => true,
        ]);

        ServiceRequest::query()->create([
            'user_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'reference_number' => 'KHR-TEST-001',
            'tracking_token' => ServiceRequest::generateTrackingToken(),
            'status' => 'pending',
        ]);

        $this->actingAs($staff);

        $this->deleteJson("/api/staff/services/{$service->id}")
            ->assertStatus(422);
    }

    private function createOffice(string $name): Office
    {
        return Office::query()->create([
            'name' => $name,
            'address' => 'Main',
            'is_active' => true,
        ]);
    }

    private function createCategory(): ServiceCategory
    {
        return ServiceCategory::query()->create([
            'name' => 'Municipality Services',
            'is_active' => true,
        ]);
    }
}
