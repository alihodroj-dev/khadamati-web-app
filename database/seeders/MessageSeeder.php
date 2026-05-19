<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        $citizen = User::query()->where('email', 'citizen@khadamati.com')->first();
        $staff = User::query()->where('email', 'staff@khadamati.com')->first();
        $conversation = Conversation::query()->first();

        if (! $conversation || ! $citizen || ! $staff) {
            return;
        }

        $messages = [
            [
                'sender_id' => $citizen->id,
                'receiver_id' => $staff->id,
                'sender_type' => 'citizen',
                'message' => 'Hello, I submitted my birth certificate request. Can you confirm you received all documents?',
                'is_read' => true,
                'read_at' => now()->subHours(5),
                'created_at' => now()->subHours(6),
            ],
            [
                'sender_id' => $staff->id,
                'receiver_id' => $citizen->id,
                'sender_type' => 'staff',
                'message' => 'Hi Jane, we received your national ID. Please upload a clearer family registration scan.',
                'is_read' => true,
                'read_at' => now()->subHours(3),
                'created_at' => now()->subHours(4),
            ],
            [
                'sender_id' => $citizen->id,
                'receiver_id' => $staff->id,
                'sender_type' => 'citizen',
                'message' => 'Thanks, I will upload an updated copy today.',
                'is_read' => false,
                'created_at' => now()->subHours(2),
            ],
        ];

        foreach ($messages as $data) {
            Message::updateOrCreate(
                [
                    'conversation_id' => $conversation->id,
                    'sender_id' => $data['sender_id'],
                    'message' => $data['message'],
                ],
                array_merge($data, ['conversation_id' => $conversation->id])
            );
        }

        $conversation->update(['last_message_at' => now()->subHours(2)]);
    }
}
