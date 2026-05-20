<?php

namespace Database\Seeders;

use App\Models\Municipality;
use App\Models\Office;
use Database\Seeders\Concerns\SeedImageUrls;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    use SeedImageUrls;
    public function run(): void
    {
        $beirut = Municipality::where('code', 'BEY')->first();
        $tripoli = Municipality::where('code', 'TRP')->first();
        $sidon = Municipality::where('code', 'SID')->first();

        $offices = [
            [
                'municipality' => $beirut,
                'name' => 'Beirut Central Services Office',
                'address' => 'Martyrs Square, Beirut',
                'phone' => '+96111234001',
                'email' => 'central@beirut.khadamati.lb',
                'latitude' => 33.8938,
                'longitude' => 35.5018,
                'working_hours' => [
                    'monday' => ['09:00', '15:00'],
                    'tuesday' => ['09:00', '15:00'],
                    'wednesday' => ['09:00', '15:00'],
                    'thursday' => ['09:00', '15:00'],
                    'friday' => ['09:00', '13:00'],
                ],
                'is_active' => true,
            ],
            [
                'municipality' => $beirut,
                'name' => 'Hamra Citizen Service Center',
                'address' => 'Hamra Street, Beirut',
                'phone' => '+96111234002',
                'email' => 'hamra@beirut.khadamati.lb',
                'latitude' => 33.8959,
                'longitude' => 35.4851,
                'working_hours' => [
                    'monday' => ['09:00', '15:00'],
                    'tuesday' => ['09:00', '15:00'],
                    'wednesday' => ['09:00', '15:00'],
                    'thursday' => ['09:00', '15:00'],
                    'friday' => ['09:00', '13:00'],
                ],
                'is_active' => true,
            ],
            [
                'municipality' => $tripoli,
                'name' => 'Tripoli Main Office',
                'address' => 'Tell Square, Tripoli',
                'phone' => '+96161234001',
                'email' => 'main@tripoli.khadamati.lb',
                'latitude' => 34.4367,
                'longitude' => 35.8497,
                'working_hours' => [
                    'monday' => ['08:30', '14:30'],
                    'tuesday' => ['08:30', '14:30'],
                    'wednesday' => ['08:30', '14:30'],
                    'thursday' => ['08:30', '14:30'],
                    'friday' => ['08:30', '12:30'],
                ],
                'is_active' => true,
            ],
            [
                'municipality' => $sidon,
                'name' => 'Sidon Services Office',
                'address' => 'Old Souks, Sidon',
                'phone' => '+96171234001',
                'email' => 'services@sidon.khadamati.lb',
                'latitude' => 33.5575,
                'longitude' => 35.3719,
                'working_hours' => [
                    'monday' => ['09:00', '15:00'],
                    'tuesday' => ['09:00', '15:00'],
                    'wednesday' => ['09:00', '15:00'],
                    'thursday' => ['09:00', '15:00'],
                    'friday' => ['09:00', '13:00'],
                ],
                'is_active' => true,
            ],
        ];

        foreach ($offices as $office) {
            if (! $office['municipality']) {
                continue;
            }

            Office::updateOrCreate(
                [
                    'municipality_id' => $office['municipality']->id,
                    'name' => $office['name'],
                ],
                [
                    'address' => $office['address'],
                    'phone' => $office['phone'],
                    'email' => $office['email'],
                    'latitude' => $office['latitude'],
                    'longitude' => $office['longitude'],
                    'working_hours' => $office['working_hours'],
                    'is_active' => $office['is_active'],
                    'image_url' => $this->officeImageUrl($office['name']),
                ]
            );
        }
    }
}
