<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $citizen = User::query()->where('email', 'citizen@khadamati.com')->first();
        $staff = User::query()->where('email', 'staff@khadamati.com')->first();

        $buildingRequest = ServiceRequest::query()
            ->where('reference_number', 'like', '%SEED003')
            ->first();

        if (! $buildingRequest || ! $citizen) {
            return;
        }

        Appointment::updateOrCreate(
            [
                'service_request_id' => $buildingRequest->id,
            ],
            [
                'user_id' => $citizen->id,
                'staff_id' => $staff?->id,
                'appointment_date' => now()->addDays(3)->toDateString(),
                'appointment_time' => '10:00:00',
                'status' => 'scheduled',
                'notes' => 'Bring original property deed.',
            ]
        );
    }
}
