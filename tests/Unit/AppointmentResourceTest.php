<?php

namespace Tests\Unit;

use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Office;
use App\Models\Service;
use App\Models\ServiceRequest;
use Carbon\Carbon;
use Tests\TestCase;

class AppointmentResourceTest extends TestCase
{
    public function test_includes_service_request_tracking_fields_and_formatted_dates(): void
    {
        $office = new Office([
            'id' => 2,
            'name' => 'Beirut Main Office',
            'address' => 'Hamra Street',
            'latitude' => 33.8938,
            'longitude' => 35.5018,
        ]);

        $service = new Service([
            'id' => 5,
            'name' => 'Passport Renewal',
        ]);

        $serviceRequest = new ServiceRequest([
            'id' => 12,
            'reference_number' => 'KHR-20260516-ABCDEF',
            'tracking_token' => 'abc123token',
            'status' => 'under_review',
        ]);
        $serviceRequest->setRelation('office', $office);
        $serviceRequest->setRelation('service', $service);

        $appointment = new Appointment([
            'id' => 1,
            'status' => 'scheduled',
            'appointment_date' => Carbon::parse('2026-05-20'),
            'appointment_time' => '09:00:00',
            'notes' => 'Bring ID',
        ]);
        $appointment->created_at = Carbon::parse('2026-05-16T10:00:00Z');
        $appointment->updated_at = Carbon::parse('2026-05-16T11:00:00Z');
        $appointment->setRelation('serviceRequest', $serviceRequest);

        $payload = (new AppointmentResource($appointment))->resolve();

        $this->assertSame('2026-05-20', $payload['appointment_date']);
        $this->assertSame('09:00', $payload['appointment_time']);
        $this->assertSame('KHR-20260516-ABCDEF', $payload['service_request']['reference_number']);
        $this->assertSame('abc123token', $payload['service_request']['tracking_token']);
        $this->assertArrayNotHasKey('tracking_number', $payload['service_request']);
        $this->assertSame('Beirut Main Office', $payload['office']['name']);
        $this->assertSame(33.8938, $payload['office']['latitude']);
        $this->assertSame('Passport Renewal', $payload['service']['name']);
        $this->assertSame('2026-05-16T10:00:00.000000Z', $payload['created_at']);
    }
}
