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

        $completeCitizen = User::updateOrCreate(
            ['email' => 'citizen.complete@khadamati.com'],
            [
                'name' => 'Ali Hodroj',
                'first_name' => 'Ali',
                'last_name' => 'Hodroj',
                'father_name' => 'Salah',
                'mother_name' => 'Fatima Alyan',
                'date_of_birth' => '2004-11-27',
                'password' => null,
                'role' => User::ROLE_CITIZEN,
                'phone' => '+96176554042',
                'national_id' => '00073028821',
                'id_front_path' => 'id-documents/seed-citizen/front.jpg',
                'id_back_path' => 'id-documents/seed-citizen/back.jpg',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        UserProfileCompletion::sync($completeCitizen);

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
    }
}
