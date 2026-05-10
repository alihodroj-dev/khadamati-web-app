<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@khadamati.test'],
            [
                'name' => 'Khadamati Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'phone' => '+96170000001',
                'national_id' => 'ADM-000001',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff@khadamati.test'],
            [
                'name' => 'Khadamati Staff',
                'password' => Hash::make('password123'),
                'role' => 'staff',
                'phone' => '+96170000002',
                'national_id' => 'STF-000001',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'citizen@khadamati.test'],
            [
                'name' => 'Test Citizen',
                'password' => Hash::make('password123'),
                'role' => 'citizen',
                'phone' => '+96170000003',
                'national_id' => 'CTZ-000001',
                'is_active' => true,
            ]
        );
    }
}
