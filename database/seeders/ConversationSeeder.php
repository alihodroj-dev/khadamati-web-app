<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\ServiceRequest;
use Illuminate\Database\Seeder;

class ConversationSeeder extends Seeder
{
    public function run(): void
    {
        ServiceRequest::query()
            ->whereNotNull('assigned_staff_id')
            ->orderBy('id')
            ->each(function (ServiceRequest $request) {
                $status = in_array($request->status, ['completed', 'cancelled'], true)
                    ? 'closed'
                    : 'active';

                Conversation::updateOrCreate(
                    [
                        'service_request_id' => $request->id,
                        'citizen_id' => $request->user_id,
                    ],
                    [
                        'staff_id' => $request->assigned_staff_id,
                        'status' => $status,
                        'last_message_at' => now()->subHours(2),
                    ]
                );
            });
    }
}
