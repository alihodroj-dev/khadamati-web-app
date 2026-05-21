<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Office;
use App\Models\OfficeTimeSlot;
use App\Models\OfficeTimeSlotBlock;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StaffOfficeTimeSlotTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function staff_can_manage_time_slots_for_their_office(): void
    {
        $office = $this->createOffice();
        $staff = $this->createStaff($office);

        $this->actingAs($staff);

        $this->postJson('/api/staff/time-slots', [
            'day_of_week' => 0,
            'start_time' => '10:00',
            'end_time' => '14:00',
            'slot_duration_minutes' => 30,
            'is_active' => true,
        ])
            ->assertCreated()
            ->assertJsonPath('data.day_of_week', 0)
            ->assertJsonPath('data.start_time', '10:00')
            ->assertJsonPath('data.end_time', '14:00');

        $slot = OfficeTimeSlot::query()->sole();

        $this->getJson('/api/staff/time-slots')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->patchJson("/api/staff/time-slots/{$slot->id}", [
            'end_time' => '15:00',
        ])
            ->assertOk()
            ->assertJsonPath('data.end_time', '15:00');

        $this->deleteJson("/api/staff/time-slots/{$slot->id}")
            ->assertOk();

        $this->assertDatabaseMissing('office_time_slots', ['id' => $slot->id]);
    }

    #[Test]
    public function staff_cannot_manage_time_slots_for_another_office(): void
    {
        $officeA = $this->createOffice('Office A');
        $officeB = $this->createOffice('Office B');
        $staffA = $this->createStaff($officeA);

        $slotAtB = OfficeTimeSlot::query()->create([
            'office_id' => $officeB->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'slot_duration_minutes' => 30,
            'is_active' => true,
        ]);

        $this->actingAs($staffA);

        $this->patchJson("/api/staff/time-slots/{$slotAtB->id}", [
            'start_time' => '08:00',
        ])->assertForbidden();
    }

    #[Test]
    public function availability_prefers_active_office_time_slots(): void
    {
        $office = $this->createOffice();
        $office->update([
            'working_hours' => [
                'sun' => '08:00-20:00',
            ],
        ]);

        OfficeTimeSlot::query()->create([
            'office_id' => $office->id,
            'day_of_week' => 0,
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'slot_duration_minutes' => 30,
            'is_active' => true,
        ]);

        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $serviceRequest = $this->createServiceRequest($citizen, $office);

        $this->actingAs($citizen);

        $this->getJson('/api/appointments/availability?date=2026-05-17&service_request_id='.$serviceRequest->id)
            ->assertOk()
            ->assertJsonPath('data.source', 'time_slots')
            ->assertJsonPath('data.working_hours.start', '10:00')
            ->assertJsonPath('data.working_hours.end', '12:00')
            ->assertJsonPath('data.available_slots', ['10:00', '11:00']);
    }

    #[Test]
    public function availability_falls_back_to_working_hours_when_no_slots(): void
    {
        $office = $this->createOffice();
        $office->update([
            'working_hours' => [
                'sun' => '10:00-12:00',
            ],
        ]);

        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $serviceRequest = $this->createServiceRequest($citizen, $office);

        $this->actingAs($citizen);

        $this->getJson('/api/appointments/availability?date=2026-05-17&service_request_id='.$serviceRequest->id)
            ->assertOk()
            ->assertJsonPath('data.source', 'working_hours')
            ->assertJsonPath('data.available_slots', ['10:00', '11:00']);
    }

    #[Test]
    public function availability_requires_service_request_id(): void
    {
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);

        $this->actingAs($citizen);

        $this->getJson('/api/appointments/availability?date=2026-05-17')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['service_request_id']);
    }

    #[Test]
    public function time_slot_blocks_mark_times_unavailable(): void
    {
        $office = $this->createOffice();
        $staff = $this->createStaff($office);

        OfficeTimeSlot::query()->create([
            'office_id' => $office->id,
            'day_of_week' => 0,
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'slot_duration_minutes' => 30,
            'is_active' => true,
        ]);

        $this->actingAs($staff);

        $this->postJson('/api/staff/time-slot-blocks', [
            'date' => '2026-05-17',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'reason' => 'Staff meeting',
        ])->assertCreated();

        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $serviceRequest = $this->createServiceRequest($citizen, $office);

        $this->actingAs($citizen);

        $response = $this->getJson(
            '/api/appointments/availability?date=2026-05-17&service_request_id='.$serviceRequest->id
        )->assertOk();

        $this->assertContains('10:00', $response->json('data.unavailable_slots'));
        $this->assertNotContains('10:00', $response->json('data.available_slots'));
        $this->assertContains('09:00', $response->json('data.available_slots'));
    }

    #[Test]
    public function booking_rejects_times_outside_available_slots(): void
    {
        $office = $this->createOffice();

        OfficeTimeSlot::query()->create([
            'office_id' => $office->id,
            'day_of_week' => 0,
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'slot_duration_minutes' => 30,
            'is_active' => true,
        ]);

        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $serviceRequest = $this->createServiceRequest($citizen, $office);

        $this->actingAs($citizen);

        $appointmentDate = now()->addDays(3)->toDateString();

        $this->postJson('/api/appointments', [
            'service_request_id' => $serviceRequest->id,
            'appointment_date' => $appointmentDate,
            'appointment_time' => '09:00',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['appointment_time']);

        $this->postJson('/api/appointments', [
            'service_request_id' => $serviceRequest->id,
            'appointment_date' => $appointmentDate,
            'appointment_time' => '10:00',
        ])->assertCreated();
    }

    #[Test]
    public function staff_specific_slots_apply_when_staff_is_selected(): void
    {
        $office = $this->createOffice();
        $staff = $this->createStaff($office);

        OfficeTimeSlot::query()->create([
            'office_id' => $office->id,
            'staff_id' => null,
            'day_of_week' => 0,
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'slot_duration_minutes' => 30,
            'is_active' => true,
        ]);

        OfficeTimeSlot::query()->create([
            'office_id' => $office->id,
            'staff_id' => $staff->id,
            'day_of_week' => 0,
            'start_time' => '14:00:00',
            'end_time' => '15:00:00',
            'slot_duration_minutes' => 30,
            'is_active' => true,
        ]);

        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $serviceRequest = $this->createServiceRequest($citizen, $office);
        $serviceRequest->update(['assigned_staff_id' => $staff->id]);

        $this->actingAs($citizen);

        $response = $this->getJson(
            '/api/appointments/availability?date=2026-05-17&service_request_id='.$serviceRequest->id
        )->assertOk()
            ->assertJsonPath('data.source', 'time_slots');

        $times = $response->json('data.available_slots');

        $this->assertContains('09:00', $times);
        $this->assertContains('14:00', $times);
    }

    private function createOffice(string $name = 'Main Office'): Office
    {
        return Office::query()->create([
            'name' => $name,
            'address' => 'Main Street',
            'is_active' => true,
        ]);
    }

    private function createStaff(Office $office): User
    {
        return User::factory()->create([
            'role' => User::ROLE_STAFF,
            'office_id' => $office->id,
            'is_active' => true,
        ]);
    }

    private function createServiceRequest(User $citizen, Office $office): ServiceRequest
    {
        $category = ServiceCategory::query()->create([
            'name' => 'Civil',
            'is_active' => true,
        ]);

        $service = Service::query()->create([
            'service_category_id' => $category->id,
            'office_id' => $office->id,
            'name' => 'Permit',
            'base_fee' => 10,
            'requires_appointment' => true,
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
