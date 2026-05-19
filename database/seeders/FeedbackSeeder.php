<?php

namespace Database\Seeders;

use App\Models\Feedback;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class FeedbackSeeder extends Seeder
{
    public function run(): void
    {
        $citizen = User::query()->where('email', 'citizen.complete@khadamati.com')->first()
            ?? User::query()->where('email', 'citizen@khadamati.com')->first();

        $completedRequest = ServiceRequest::query()
            ->where('reference_number', 'like', '%SEED004')
            ->first();

        if (! $completedRequest || ! $citizen) {
            return;
        }

        Feedback::updateOrCreate(
            ['service_request_id' => $completedRequest->id],
            [
                'user_id' => $citizen->id,
                'rating' => 5,
                'comment' => 'Fast processing and helpful staff. Thank you!',
            ]
        );
    }
}
