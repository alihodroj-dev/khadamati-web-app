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

    public function test_availability_returns_slots_and_working_hours(): void
    {
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);

        $response = $this->actingAs($citizen)->getJson(
            '/api/appointments/availability?date=2026-05-17'
        );

        $response->assertOk()
            ->assertJsonPath('data.date', '2026-05-17')
            ->assertJsonPath('data.slot_duration_minutes', 30)
            ->assertJsonPath('data.working_hours.start', '09:00')
            ->assertJsonPath('data.working_hours.end', '15:00')
            ->assertJsonStructure([
                'data' => [
                    'available_times',
                    'unavailable_times',
                ],
            ]);

        $this->assertContains('09:00', $response->json('data.available_times'));
        $this->assertContains('09:30', $response->json('data.available_times'));
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
            ->assertJsonPath('data.available_times', ['10:00', '10:30', '11:00', '11:30']);
    }

    public function test_booked_slot_appears_in_unavailable_times(): void
    {
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $serviceRequest = $this->createServiceRequest($citizen);

        Appointment::query()->create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => $citizen->id,
            'staff_id' => $staff->id,
            'appointment_date' => '2026-05-17',
            'appointment_time' => '10:00:00',
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($citizen)->getJson(
            '/api/appointments/availability?date=2026-05-17&staff_id='.$staff->id
        );

        $response->assertOk()
            ->assertJsonPath('data.unavailable_times', ['10:00']);

        $this->assertNotContains('10:00', $response->json('data.available_times'));
    }

    private function createServiceRequest(User $citizen, ?Office $office = null): ServiceRequest
    {
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
            'is_active' => true,
        ]);

        return ServiceRequest::query()->create([
            'user_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'reference_number' => 'REQ-'.uniqid(),
            'tracking_token' => ServiceRequest::generateTrackingToken(),
            'status' => 'pending',
        ]);
    }
}
