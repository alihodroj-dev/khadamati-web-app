<?php

namespace Database\Seeders;

use App\Models\Feedback;
use App\Models\FeedbackResponse;
use App\Models\User;
use Illuminate\Database\Seeder;

class FeedbackResponseSeeder extends Seeder
{
    public function run(): void
    {
        $staff = User::query()->where('email', 'staff@khadamati.com')->first();
        $feedback = Feedback::query()
            ->whereHas('serviceRequest', fn ($q) => $q->where('reference_number', 'like', '%SEED004'))
            ->first();

        if (! $feedback || ! $staff) {
            return;
        }

        FeedbackResponse::updateOrCreate(
            [
                'feedback_id' => $feedback->id,
                'responder_id' => $staff->id,
                'visibility' => FeedbackResponse::VISIBILITY_PUBLIC,
            ],
            [
                'message' => 'Thank you for your feedback. We are glad we could help!',
            ]
        );
    }
}
