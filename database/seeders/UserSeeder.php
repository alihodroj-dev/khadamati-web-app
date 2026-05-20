<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\User;
use App\Support\UserProfileCompletion;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $centralOffice = Office::query()->where('name', 'Beirut Central Services Office')->first();
        $hamraOffice = Office::query()->where('name', 'Hamra Citizen Service Center')->first();

        User::updateOrCreate(
            ['email' => 'admin@khadamati.com'],
            [
                'name' => 'System Admin',
                'password' => bcrypt('password'),
                'role' => User::ROLE_ADMIN,
                'phone' => '+96170000001',
                'national_id' => 'ADM-000001',
                'is_active' => true,
                'profile_completed' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff@khadamati.com'],
            [
                'name' => 'Nadia Staff',
                'password' => bcrypt('password'),
                'role' => User::ROLE_STAFF,
                'office_id' => $centralOffice?->id,
                'phone' => '+96170000002',
                'national_id' => 'STF-000001',
                'is_active' => true,
                'profile_completed' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff2@khadamati.com'],
            [
                'name' => 'Karim Staff',
                'password' => bcrypt('password'),
                'role' => User::ROLE_STAFF,
                'office_id' => $hamraOffice?->id ?? $centralOffice?->id,
                'phone' => '+96170000004',
                'national_id' => 'STF-000002',
                'is_active' => true,
                'profile_completed' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'citizen@khadamati.com'],
            [
                'name' => 'Jane Citizen',
                'password' => null,
                'role' => User::ROLE_CITIZEN,
                'phone' => '+96170000003',
                'national_id' => 'CTZ-000001',
                'is_active' => true,
                'email_verified_at' => now(),
                'profile_completed' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'citizen2@khadamati.com'],
            [
                'name' => 'Sara Mansour',
                'password' => null,
                'role' => User::ROLE_CITIZEN,
                'phone' => '+96170000005',
                'national_id' => 'CTZ-000002',
                'is_active' => true,
                'email_verified_at' => now(),
                'profile_completed' => false,
            ]
        );
                User::updateOrCreate(
            ['email' => 'hussein.barakat.313371@gmail.com'],
            [
                'name' => 'hussein baraket',
                'password' => null,
                'role' => User::ROLE_STAFF,
                'phone' => '+96170887097',
                'national_id' => 'STF-000003',
                'is_active' => true,
                'email_verified_at' => now(),
                'profile_completed' => false,
            ]
        );

    }
}
