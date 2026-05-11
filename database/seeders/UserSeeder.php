<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@khadamati.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'role' => User::ROLE_ADMIN,
                'phone' => '+96170000001',
                'national_id' => 'ADM-000001',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff@khadamati.com'],
            [
                'name' => 'Staff User',
                'password' => bcrypt('password'),
                'role' => User::ROLE_STAFF,
                'phone' => '+96170000002',
                'national_id' => 'STF-000001',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'citizen@khadamati.com'],
            [
                'name' => 'Citizen User',
                'password' => bcrypt('password'),
                'role' => User::ROLE_CITIZEN,
                'phone' => '+96170000003',
                'national_id' => 'CTZ-000001',
                'is_active' => true,
            ]
        );
    }
}