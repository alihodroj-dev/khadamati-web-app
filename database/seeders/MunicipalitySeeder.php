<?php

namespace Database\Seeders;

use App\Models\Municipality;
use Illuminate\Database\Seeder;

class MunicipalitySeeder extends Seeder
{
    public function run(): void
    {
        $municipalities = [
            [
                'name' => 'Beirut Municipality',
                'code' => 'BEY',
                'address' => 'Beirut, Lebanon',
                'phone' => '+9611123456',
                'email' => 'info@beirut.gov.lb',
                'is_active' => true,
            ],
            [
                'name' => 'Tripoli Municipality',
                'code' => 'TRP',
                'address' => 'Tripoli, Lebanon',
                'phone' => '+9616123456',
                'email' => 'info@tripoli.gov.lb',
                'is_active' => true,
            ],
            [
                'name' => 'Sidon Municipality',
                'code' => 'SID',
                'address' => 'Sidon, Lebanon',
                'phone' => '+9617123456',
                'email' => 'info@sidon.gov.lb',
                'is_active' => true,
            ],
        ];

        foreach ($municipalities as $municipality) {
            Municipality::updateOrCreate(
                ['code' => $municipality['code']],
                $municipality
            );
        }
    }
}
