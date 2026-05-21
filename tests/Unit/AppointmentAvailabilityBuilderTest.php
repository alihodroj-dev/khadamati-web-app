<?php

namespace Tests\Unit;

use App\Models\Office;
use App\Support\AppointmentAvailabilityBuilder;
use Tests\TestCase;

class AppointmentAvailabilityBuilderTest extends TestCase
{
    public function test_builds_default_slots_and_marks_unavailable(): void
    {
        $builder = new AppointmentAvailabilityBuilder;

        $result = $builder->build('2026-05-17', null, ['10:00']);

        $this->assertSame('2026-05-17', $result['date']);
        $this->assertSame(60, $result['slot_duration_minutes']);
        $this->assertSame(['start' => '09:00', 'end' => '15:00'], $result['working_hours']);
        $this->assertContains('09:00', $result['available_slots']);
        $this->assertContains('11:00', $result['available_slots']);
        $this->assertContains('10:00', $result['unavailable_slots']);
        $this->assertNotContains('10:00', $result['available_slots']);
    }

    public function test_uses_office_working_hours_for_weekday(): void
    {
        $builder = new AppointmentAvailabilityBuilder;

        $office = new Office([
            'working_hours' => [
                'sun' => '10:00-14:00',
            ],
        ]);

        $result = $builder->build('2026-05-17', $office, []);

        $this->assertSame('working_hours', $result['source']);
        $this->assertSame(['start' => '10:00', 'end' => '14:00'], $result['working_hours']);
        $this->assertSame(['10:00', '11:00', '12:00', '13:00'], $result['available_slots']);
    }

    public function test_occupied_slots_cover_one_hour_per_appointment(): void
    {
        $builder = new AppointmentAvailabilityBuilder;

        $occupied = $builder->occupiedSlotsForAppointments('2026-05-17', ['10:00']);

        $this->assertSame(['10:00'], $occupied);
    }
}
