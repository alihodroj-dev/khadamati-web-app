<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\OfficeTimeSlot;
use App\Models\User;
use Illuminate\Database\Seeder;

class OfficeTimeSlotSeeder extends Seeder
{
    public function run(): void
    {
        $office = Office::query()->where('name', 'Beirut Central Services Office')->first();
        $staff = User::query()->where('email', 'staff@khadamati.com')->first();

        if (! $office) {
            return;
        }

        foreach ([1, 2, 3, 4, 5] as $dayOfWeek) {
            OfficeTimeSlot::updateOrCreate(
                [
                    'office_id' => $office->id,
                    'staff_id' => $staff?->id,
                    'day_of_week' => $dayOfWeek,
                ],
                [
                    'start_time' => '09:00:00',
                    'end_time' => '15:00:00',
                    'slot_duration_minutes' => 30,
                    'is_active' => true,
                ]
            );
        }
    }
}
