<?php

namespace App\Notifications;

use App\Channels\FcmChannel;
use App\Models\FeedbackResponse;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FeedbackResponseNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly FeedbackResponse $response,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (filled($notifiable->fcm_token)) {
            $channels[] = FcmChannel::class;
        }

        return $channels;
    }

    public function toFcm(object $notifiable): array
    {
        return [
            'title' => 'New reply to your feedback',
            'body' => 'Staff has replied to your feedback.',
            'data' => [
                'type' => 'feedback_response',
                'feedback_id' => (string) $this->response->feedback_id,
                'feedback_response_id' => (string) $this->response->id,
            ],
        ];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'feedback_response',
            'title' => 'New reply to your feedback',
            'body' => 'Staff has replied to your feedback.',
            'feedback_id' => $this->response->feedback_id,
            'feedback_response_id' => $this->response->id,
            'service_request_id' => $this->response->feedback?->service_request_id,
        ];
    }
}
