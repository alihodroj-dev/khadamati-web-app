<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class ConversationSeeder extends Seeder
{
    public function run(): void
    {
        $citizen = User::query()->where('email', 'citizen@khadamati.com')->first();
        $staff = User::query()->where('email', 'staff@khadamati.com')->first();

        $underReview = ServiceRequest::query()
            ->where('reference_number', 'like', '%SEED001')
            ->first();

        if (! $underReview || ! $citizen) {
            return;
        }

        Conversation::updateOrCreate(
            ['service_request_id' => $underReview->id],
            [
                'citizen_id' => $citizen->id,
                'staff_id' => $staff?->id,
                'status' => 'active',
                'last_message_at' => now()->subHours(2),
            ]
        );
    }
}
