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

class StaffOfficeAccessTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function staff_from_office_a_cannot_manage_office_b_requests(): void
    {
        $officeA = $this->createOffice('Office A');
        $officeB = $this->createOffice('Office B');

        $staffA = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'office_id' => $officeA->id,
            'is_active' => true,
        ]);

        $requestAtOfficeB = $this->createServiceRequestAtOffice($officeB);

        $this->actingAs($staffA);

        $this->getJson('/api/staff/requests')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->getJson("/api/staff/requests/{$requestAtOfficeB->id}")
            ->assertForbidden();

        $this->patchJson("/api/staff/requests/{$requestAtOfficeB->id}/status", [
            'status' => 'under_review',
        ])->assertForbidden();
    }

    #[Test]
    public function staff_from_office_a_can_manage_office_a_requests(): void
    {
        $officeA = $this->createOffice('Office A');
        $staffA = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'office_id' => $officeA->id,
            'is_active' => true,
        ]);

        $requestAtOfficeA = $this->createServiceRequestAtOffice($officeA);

        $this->actingAs($staffA);

        $this->getJson('/api/staff/requests')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $requestAtOfficeA->id);

        $this->getJson("/api/staff/requests/{$requestAtOfficeA->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $requestAtOfficeA->id);

        $this->patchJson("/api/staff/requests/{$requestAtOfficeA->id}/status", [
            'status' => 'under_review',
        ])->assertOk();
    }

    #[Test]
    public function admin_can_manage_requests_from_any_office(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $officeA = $this->createOffice('Office A');
        $officeB = $this->createOffice('Office B');

        $requestA = $this->createServiceRequestAtOffice($officeA);
        $requestB = $this->createServiceRequestAtOffice($officeB);

        $this->actingAs($admin);

        $this->getJson('/api/staff/requests')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->getJson("/api/staff/requests/{$requestA->id}")->assertOk();
        $this->getJson("/api/staff/requests/{$requestB->id}")->assertOk();
    }

    #[Test]
    public function citizen_my_requests_access_is_unchanged(): void
    {
        $citizen = User::factory()->create([
            'role' => User::ROLE_CITIZEN,
            'is_active' => true,
        ]);

        $office = $this->createOffice('Office A');
        $otherCitizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);

        $ownRequest = $this->createServiceRequestAtOffice($office, $citizen);
        $otherRequest = $this->createServiceRequestAtOffice($office, $otherCitizen);

        $this->actingAs($citizen);

        $this->getJson('/api/my-requests')
            ->assertOk()
            ->assertJsonCount(1, 'data.service_requests');

        $this->getJson("/api/my-requests/{$ownRequest->id}")
            ->assertOk()
            ->assertJsonPath('data.service_request.id', $ownRequest->id);

        $this->getJson("/api/my-requests/{$otherRequest->id}")
            ->assertForbidden();
    }

    #[Test]
    public function admin_api_requires_office_id_when_creating_staff_user(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $office = $this->createOffice('Office A');

        $this->actingAs($admin);

        $this->postJson('/api/admin/users', [
            'name' => 'Office Staff',
            'email' => 'staff.office@example.com',
            'password' => 'password123',
            'role' => User::ROLE_STAFF,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['office_id']);

        $this->postJson('/api/admin/users', [
            'name' => 'Office Staff',
            'email' => 'staff.office@example.com',
            'password' => 'password123',
            'role' => User::ROLE_STAFF,
            'office_id' => $office->id,
        ])
            ->assertCreated()
            ->assertJsonPath('data.office_id', $office->id)
            ->assertJsonPath('data.office.id', $office->id);
    }

    #[Test]
    public function admin_can_activate_and_deactivate_staff_and_citizens(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $office = $this->createOffice('Office A');

        $staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'office_id' => $office->id,
            'is_active' => true,
        ]);

        $citizen = User::factory()->create([
            'role' => User::ROLE_CITIZEN,
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        $this->patchJson("/api/admin/users/{$staff->id}/activation", [
            'is_active' => false,
        ])
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->patchJson("/api/admin/users/{$citizen->id}/activation", [
            'is_active' => false,
        ])
            ->assertOk()
            ->assertJsonPath('data.is_active', false);
    }

    private function createOffice(string $name): Office
    {
        return Office::query()->create([
            'name' => $name,
            'address' => 'Main Street',
            'is_active' => true,
        ]);
    }

    private function createServiceRequestAtOffice(Office $office, ?User $citizen = null): ServiceRequest
    {
        $citizen ??= User::factory()->create(['role' => User::ROLE_CITIZEN]);

        $category = ServiceCategory::query()->create([
            'name' => 'Civil',
            'is_active' => true,
        ]);

        $service = Service::query()->create([
            'service_category_id' => $category->id,
            'office_id' => $office->id,
            'name' => 'Permit',
            'base_fee' => 10,
            'is_active' => true,
        ]);

        return ServiceRequest::query()->create([
            'user_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'reference_number' => 'KHR-'.uniqid(),
            'tracking_token' => ServiceRequest::generateTrackingToken(),
            'status' => 'pending',
        ]);
    }
}
