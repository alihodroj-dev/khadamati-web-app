<?php

namespace Database\Seeders;

use App\Models\Conversation;
use Database\Seeders\Concerns\SeedsConversationMessages;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    use SeedsConversationMessages;

    public function run(): void
    {
        $variant = 0;

        Conversation::query()
            ->whereNotNull('staff_id')
            ->orderBy('id')
            ->each(function (Conversation $conversation) use (&$variant) {
                $this->seedMessagesForConversation($conversation, $variant);
                $variant++;
            });
    }
}
