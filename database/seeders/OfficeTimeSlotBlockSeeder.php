<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\OfficeTimeSlotBlock;
use App\Models\User;
use Illuminate\Database\Seeder;

class OfficeTimeSlotBlockSeeder extends Seeder
{
    public function run(): void
    {
        $office = Office::query()->where('name', 'Beirut Central Services Office')->first();
        $staff = User::query()->where('email', 'staff@khadamati.com')->first();

        if (! $office) {
            return;
        }

        OfficeTimeSlotBlock::updateOrCreate(
            [
                'office_id' => $office->id,
                'date' => now()->addWeek()->toDateString(),
                'start_time' => '12:00:00',
            ],
            [
                'staff_id' => $staff?->id,
                'end_time' => '13:00:00',
                'reason' => 'Staff lunch break',
            ]
        );
    }
}
