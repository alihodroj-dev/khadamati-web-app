<?php

namespace Database\Seeders;

use App\Models\OtpChallenge;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class OtpChallengeSeeder extends Seeder
{
    public function run(): void
    {
        $citizen = User::query()->where('email', 'citizen@khadamati.com')->first();
        $citizen2 = User::query()->where('email', 'citizen2@khadamati.com')->first();

        if ($citizen) {
            OtpChallenge::updateOrCreate(
                ['challenge_token' => 'seed-consumed-otp-challenge'],
                [
                    'user_id' => $citizen->id,
                    'otp_hash' => Hash::make('123456'),
                    'expires_at' => now()->subHour(),
                    'consumed_at' => now()->subHours(2),
                    'attempts' => 1,
                    'channel' => OtpChallenge::CHANNEL_EMAIL,
                ]
            );
        }

        if ($citizen2) {
            OtpChallenge::updateOrCreate(
                ['challenge_token' => 'seed-active-otp-challenge'],
                [
                    'user_id' => $citizen2->id,
                    'otp_hash' => Hash::make('654321'),
                    'expires_at' => now()->addMinutes(10),
                    'consumed_at' => null,
                    'attempts' => 0,
                    'channel' => OtpChallenge::CHANNEL_EMAIL,
                ]
            );
        }
    }
}
