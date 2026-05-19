<?php

namespace Database\Seeders;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Database\Seeder;

class SocialAccountSeeder extends Seeder
{
    public function run(): void
    {
        $completeCitizen = User::query()->where('email', 'citizen.complete@khadamati.com')->first();
        $citizen2 = User::query()->where('email', 'citizen2@khadamati.com')->first();

        if ($completeCitizen) {
            SocialAccount::updateOrCreate(
                [
                    'provider' => SocialAccount::PROVIDER_GOOGLE,
                    'provider_user_id' => 'google-seed-ali-hodroj',
                ],
                [
                    'user_id' => $completeCitizen->id,
                    'email' => $completeCitizen->email,
                    'avatar_url' => 'https://example.com/avatars/ali.jpg',
                ]
            );
        }

        if ($citizen2) {
            SocialAccount::updateOrCreate(
                [
                    'provider' => SocialAccount::PROVIDER_APPLE,
                    'provider_user_id' => 'apple-seed-sara-mansour',
                ],
                [
                    'user_id' => $citizen2->id,
                    'email' => $citizen2->email,
                    'avatar_url' => null,
                ]
            );
        }
    }
}
