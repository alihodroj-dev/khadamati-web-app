<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Office;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_availability_requires_service_request_id(): void
    {
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);

        $this->actingAs($citizen)->getJson(
            '/api/appointments/availability?date=2026-05-17'
        )->assertStatus(422)
            ->assertJsonValidationErrors(['service_request_id']);
    }

    public function test_availability_rejects_service_that_does_not_require_appointment(): void
    {
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $serviceRequest = $this->createServiceRequest($citizen, requiresAppointment: false);

        $this->actingAs($citizen)->getJson(
            '/api/appointments/availability?date=2026-05-17&service_request_id='.$serviceRequest->id
        )->assertStatus(422)
            ->assertJsonPath('message', 'This service request does not require an appointment.');
    }

    public function test_availability_returns_hourly_slots_and_working_hours(): void
    {
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $serviceRequest = $this->createServiceRequest($citizen);

        $response = $this->actingAs($citizen)->getJson(
            '/api/appointments/availability?date=2026-05-17&service_request_id='.$serviceRequest->id
        );

        $response->assertOk()
            ->assertJsonPath('data.date', '2026-05-17')
            ->assertJsonPath('data.slot_duration_minutes', 60)
            ->assertJsonPath('data.working_hours.start', '09:00')
            ->assertJsonPath('data.working_hours.end', '15:00')
            ->assertJsonStructure([
                'data' => [
                    'available_slots',
                    'unavailable_slots',
                ],
            ]);

        $this->assertContains('09:00', $response->json('data.available_slots'));
        $this->assertContains('10:00', $response->json('data.available_slots'));
        $this->assertNotContains('09:30', $response->json('data.available_slots'));
    }

    public function test_availability_uses_office_hours_from_service_request(): void
    {
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $office = Office::query()->create([
            'name' => 'Beirut',
            'address' => 'Hamra',
            'working_hours' => [
                'sun' => '10:00-12:00',
            ],
            'is_active' => true,
        ]);

        $serviceRequest = $this->createServiceRequest($citizen, $office);

        $response = $this->actingAs($citizen)->getJson(
            '/api/appointments/availability?date=2026-05-17&service_request_id='.$serviceRequest->id
        );

        $response->assertOk()
            ->assertJsonPath('data.working_hours.start', '10:00')
            ->assertJsonPath('data.working_hours.end', '12:00')
            ->assertJsonPath('data.available_slots', ['10:00', '11:00']);
    }

    public function test_booked_slot_appears_in_unavailable_slots_for_assigned_staff(): void
    {
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $serviceRequest = $this->createServiceRequest($citizen, assignedStaffId: $staff->id);

        Appointment::query()->create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => $citizen->id,
            'staff_id' => $staff->id,
            'appointment_date' => '2026-05-17',
            'appointment_time' => '10:00:00',
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($citizen)->getJson(
            '/api/appointments/availability?date=2026-05-17&service_request_id='.$serviceRequest->id
        );

        $response->assertOk()
            ->assertJsonPath('data.unavailable_slots', ['10:00']);

        $this->assertNotContains('10:00', $response->json('data.available_slots'));
    }

    public function test_store_rejects_service_that_does_not_require_appointment(): void
    {
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $serviceRequest = $this->createServiceRequest($citizen, requiresAppointment: false);

        $this->actingAs($citizen)->postJson('/api/appointments', [
            'service_request_id' => $serviceRequest->id,
            'appointment_date' => now()->addDays(3)->toDateString(),
            'appointment_time' => '10:00',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['service_request_id']);
    }

    private function createServiceRequest(
        User $citizen,
        ?Office $office = null,
        bool $requiresAppointment = true,
        ?int $assignedStaffId = null,
    ): ServiceRequest {
        $office ??= Office::query()->create([
            'name' => 'Default Office',
            'address' => 'Address',
            'is_active' => true,
        ]);

        $category = ServiceCategory::query()->create([
            'name' => 'Civil',
            'is_active' => true,
        ]);

        $service = Service::query()->create([
            'service_category_id' => $category->id,
            'office_id' => $office->id,
            'name' => 'Service',
            'base_fee' => 10,
            'requires_appointment' => $requiresAppointment,
            'is_active' => true,
        ]);

        return ServiceRequest::query()->create([
            'user_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'assigned_staff_id' => $assignedStaffId,
            'reference_number' => 'REQ-'.uniqid(),
            'tracking_token' => ServiceRequest::generateTrackingToken(),
            'status' => 'pending',
        ]);
    }
}
