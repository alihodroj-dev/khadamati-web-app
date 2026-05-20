<?php

namespace Database\Seeders\Concerns;

use App\Models\Conversation;
use App\Models\Message;
use Carbon\Carbon;

trait SeedsConversationMessages
{
    /**
     * @return list<array{sender_type: 'citizen'|'staff', message: string, is_read: bool, hours_ago: int, read_hours_ago?: int}>
     */
    protected function conversationMessageTemplates(int $variant): array
    {
        $variants = [
            [
                [
                    'sender_type' => 'citizen',
                    'message' => 'Hello, I submitted my request. Can you confirm you received all documents?',
                    'is_read' => true,
                    'hours_ago' => 6,
                    'read_hours_ago' => 5,
                ],
                [
                    'sender_type' => 'staff',
                    'message' => 'Hi, we received your documents. Please upload a clearer scan if anything is blurry.',
                    'is_read' => true,
                    'hours_ago' => 4,
                    'read_hours_ago' => 3,
                ],
                [
                    'sender_type' => 'citizen',
                    'message' => 'Thanks, I will upload an updated copy today.',
                    'is_read' => false,
                    'hours_ago' => 2,
                ],
            ],
            [
                [
                    'sender_type' => 'citizen',
                    'message' => 'I saw the status changed to requires action. What exactly is missing?',
                    'is_read' => true,
                    'hours_ago' => 8,
                    'read_hours_ago' => 7,
                ],
                [
                    'sender_type' => 'staff',
                    'message' => 'We need a recent utility bill in PDF format. The previous upload was unreadable.',
                    'is_read' => true,
                    'hours_ago' => 5,
                    'read_hours_ago' => 4,
                ],
                [
                    'sender_type' => 'citizen',
                    'message' => 'Understood. I will upload the bill within the next hour.',
                    'is_read' => false,
                    'hours_ago' => 1,
                ],
            ],
            [
                [
                    'sender_type' => 'staff',
                    'message' => 'Your application has been approved. You can collect the certificate at the office.',
                    'is_read' => true,
                    'hours_ago' => 72,
                    'read_hours_ago' => 70,
                ],
                [
                    'sender_type' => 'citizen',
                    'message' => 'Thank you! What are the office hours for pickup?',
                    'is_read' => true,
                    'hours_ago' => 48,
                    'read_hours_ago' => 47,
                ],
                [
                    'sender_type' => 'staff',
                    'message' => 'Monday to Friday, 8:30 AM – 3:30 PM. Bring your national ID.',
                    'is_read' => true,
                    'hours_ago' => 24,
                    'read_hours_ago' => 23,
                ],
            ],
            [
                [
                    'sender_type' => 'citizen',
                    'message' => 'Could you review the additional notes I added to my request?',
                    'is_read' => true,
                    'hours_ago' => 12,
                    'read_hours_ago' => 11,
                ],
                [
                    'sender_type' => 'staff',
                    'message' => 'Yes, we are reviewing them now and will update the status shortly.',
                    'is_read' => false,
                    'hours_ago' => 3,
                ],
            ],
        ];

        return $variants[$variant % count($variants)];
    }

    protected function seedMessagesForConversation(Conversation $conversation, int $variant = 0): void
    {
        if (! $conversation->staff_id) {
            return;
        }

        $citizenId = $conversation->citizen_id;
        $staffId = $conversation->staff_id;
        $latestAt = null;

        foreach ($this->conversationMessageTemplates($variant) as $template) {
            $isCitizen = $template['sender_type'] === 'citizen';
            $senderId = $isCitizen ? $citizenId : $staffId;
            $receiverId = $isCitizen ? $staffId : $citizenId;
            $createdAt = now()->subHours($template['hours_ago']);

            $attributes = [
                'conversation_id' => $conversation->id,
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'sender_type' => $template['sender_type'],
                'message' => $template['message'],
                'is_read' => $template['is_read'],
                'read_at' => $template['is_read']
                    ? now()->subHours($template['read_hours_ago'] ?? $template['hours_ago'])
                    : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];

            Message::updateOrCreate(
                [
                    'conversation_id' => $conversation->id,
                    'sender_id' => $senderId,
                    'message' => $template['message'],
                ],
                $attributes
            );

            if ($latestAt === null || $createdAt->gt($latestAt)) {
                $latestAt = $createdAt;
            }
        }

        if ($latestAt instanceof Carbon) {
            $conversation->update(['last_message_at' => $latestAt]);
        }
    }
}
