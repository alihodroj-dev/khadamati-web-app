<?php

namespace Database\Seeders;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Database\Seeder;

class DeviceTokenSeeder extends Seeder
{
    public function run(): void
    {
        $completeCitizen = User::query()->where('email', 'citizen.complete@khadamati.com')->first();
        $citizen = User::query()->where('email', 'citizen@khadamati.com')->first();

        if ($completeCitizen) {
            DeviceToken::updateOrCreate(
                [
                    'user_id' => $completeCitizen->id,
                    'token' => 'seed-ios-fcm-token-complete-citizen',
                ],
                [
                    'platform' => DeviceToken::PLATFORM_IOS,
                    'last_used_at' => now()->subDay(),
                ]
            );
        }

        if ($citizen) {
            DeviceToken::updateOrCreate(
                [
                    'user_id' => $citizen->id,
                    'token' => 'seed-ios-fcm-token-jane-citizen',
                ],
                [
                    'platform' => DeviceToken::PLATFORM_IOS,
                    'last_used_at' => now()->subDays(3),
                ]
            );
        }
    }
}
