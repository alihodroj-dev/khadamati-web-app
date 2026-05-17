<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\Payment;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StaffOfficeRequestHandlingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function office_staff_sees_unassigned_incoming_requests_for_their_office(): void
    {
        $office = $this->createOffice('Main Office');
        $otherOffice = $this->createOffice('Other Office');

        $staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'office_id' => $office->id,
            'is_active' => true,
        ]);

        $unassignedAtOffice = $this->createServiceRequestAtOffice($office);
        $assignedAtOffice = $this->createServiceRequestAtOffice($office, assignedStaff: $staff);
        $requestAtOtherOffice = $this->createServiceRequestAtOffice($otherOffice);

        $this->actingAs($staff);

        $this->getJson('/api/staff/requests')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $ids = collect($this->getJson('/api/staff/requests')->json('data'))->pluck('id');

        $this->assertTrue($ids->contains($unassignedAtOffice->id));
        $this->assertTrue($ids->contains($assignedAtOffice->id));
        $this->assertFalse($ids->contains($requestAtOtherOffice->id));

        $this->getJson("/api/staff/requests/{$unassignedAtOffice->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $unassignedAtOffice->id)
            ->assertJsonPath('data.assigned_staff_id', null);
    }

    #[Test]
    public function staff_can_filter_unassigned_office_requests(): void
    {
        $office = $this->createOffice('Main Office');

        $staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'office_id' => $office->id,
            'is_active' => true,
        ]);

        $unassigned = $this->createServiceRequestAtOffice($office);
        $this->createServiceRequestAtOffice($office, assignedStaff: $staff);

        $this->actingAs($staff);

        $this->getJson('/api/staff/requests?assignment=unassigned')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $unassigned->id);
    }

    #[Test]
    public function staff_can_update_status_for_office_request_without_prior_assignment(): void
    {
        $office = $this->createOffice('Main Office');

        $staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'office_id' => $office->id,
            'is_active' => true,
        ]);

        $request = $this->createServiceRequestAtOffice($office);

        $this->actingAs($staff);

        $this->patchJson("/api/staff/requests/{$request->id}/status", [
            'status' => 'under_review',
            'staff_notes' => 'Review started',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'under_review');

        $this->assertDatabaseHas('service_requests', [
            'id' => $request->id,
            'status' => 'under_review',
        ]);
    }

    #[Test]
    public function completing_request_creates_pending_cash_payment_not_null_method(): void
    {
        $office = $this->createOffice('Main Office');

        $staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'office_id' => $office->id,
            'is_active' => true,
        ]);

        $request = $this->createServiceRequestAtOffice($office, baseFee: 25.50);

        $this->actingAs($staff);

        $this->patchJson("/api/staff/requests/{$request->id}/status", [
            'status' => 'completed',
        ])->assertOk();

        $payment = Payment::query()->where('service_request_id', $request->id)->sole();

        $this->assertSame('pending', $payment->status);
        $this->assertSame('cash', $payment->payment_method);
        $this->assertNotNull($payment->payment_method);
        $this->assertEquals(25.50, (float) $payment->amount);
    }

    #[Test]
    public function staff_can_assign_request_to_same_office_colleague(): void
    {
        $office = $this->createOffice('Main Office');

        $staffA = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'office_id' => $office->id,
            'is_active' => true,
        ]);

        $staffB = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'office_id' => $office->id,
            'is_active' => true,
        ]);

        $request = $this->createServiceRequestAtOffice($office);

        $this->actingAs($staffA);

        $this->postJson("/api/staff/requests/{$request->id}/assign", [
            'staff_id' => $staffB->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.assigned_staff_id', $staffB->id)
            ->assertJsonPath('data.status', 'under_review');

        $this->assertDatabaseHas('service_requests', [
            'id' => $request->id,
            'assigned_staff_id' => $staffB->id,
            'status' => 'under_review',
        ]);
    }

    #[Test]
    public function staff_cannot_assign_request_to_staff_from_another_office(): void
    {
        $officeA = $this->createOffice('Office A');
        $officeB = $this->createOffice('Office B');

        $staffA = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'office_id' => $officeA->id,
            'is_active' => true,
        ]);

        $staffB = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'office_id' => $officeB->id,
            'is_active' => true,
        ]);

        $request = $this->createServiceRequestAtOffice($officeA);

        $this->actingAs($staffA);

        $this->postJson("/api/staff/requests/{$request->id}/assign", [
            'staff_id' => $staffB->id,
        ])->assertStatus(422);

        $this->assertDatabaseHas('service_requests', [
            'id' => $request->id,
            'assigned_staff_id' => null,
        ]);
    }

    #[Test]
    public function staff_cannot_update_status_for_another_office_request(): void
    {
        $officeA = $this->createOffice('Office A');
        $officeB = $this->createOffice('Office B');

        $staffA = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'office_id' => $officeA->id,
            'is_active' => true,
        ]);

        $requestAtB = $this->createServiceRequestAtOffice($officeB);

        $this->actingAs($staffA);

        $this->patchJson("/api/staff/requests/{$requestAtB->id}/status", [
            'status' => 'under_review',
        ])->assertForbidden();
    }

    private function createOffice(string $name): Office
    {
        return Office::query()->create([
            'name' => $name,
            'address' => 'Main Street',
            'is_active' => true,
        ]);
    }

    private function createServiceRequestAtOffice(
        Office $office,
        ?User $assignedStaff = null,
        float $baseFee = 10
    ): ServiceRequest {
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);

        $category = ServiceCategory::query()->create([
            'name' => 'Civil',
            'is_active' => true,
        ]);

        $service = Service::query()->create([
            'service_category_id' => $category->id,
            'office_id' => $office->id,
            'name' => 'Permit',
            'base_fee' => $baseFee,
            'is_active' => true,
        ]);

        return ServiceRequest::query()->create([
            'user_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'assigned_staff_id' => $assignedStaff?->id,
            'reference_number' => 'KHR-'.uniqid(),
            'tracking_token' => ServiceRequest::generateTrackingToken(),
            'status' => 'pending',
        ]);
    }
}
