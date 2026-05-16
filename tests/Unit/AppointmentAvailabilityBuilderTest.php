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
        $this->assertSame(30, $result['slot_duration_minutes']);
        $this->assertSame(['start' => '09:00', 'end' => '15:00'], $result['working_hours']);
        $this->assertContains('09:00', $result['available_times']);
        $this->assertContains('09:30', $result['available_times']);
        $this->assertContains('10:00', $result['unavailable_times']);
        $this->assertNotContains('10:00', $result['available_times']);
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

        $this->assertSame(['start' => '10:00', 'end' => '14:00'], $result['working_hours']);
        $this->assertSame(['10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '13:00', '13:30'], $result['available_times']);
    }
}
