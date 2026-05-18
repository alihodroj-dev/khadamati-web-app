<?php

namespace App\Notifications;

use App\Channels\FcmChannel;
use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RequestUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ServiceRequest $serviceRequest,
        private readonly string $title,
        private readonly string $body
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
            'title' => $this->title,
            'body' => $this->body,
            'data' => [
                'type' => 'request_update',
                'service_request_id' => (string) $this->serviceRequest->id,
                'reference_number' => (string) $this->serviceRequest->reference_number,
                'status' => (string) $this->serviceRequest->status,
            ],
        ];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'request_update',
            'title' => $this->title,
            'body' => $this->body,
            'service_request_id' => $this->serviceRequest->id,
            'reference_number' => $this->serviceRequest->reference_number,
            'status' => $this->serviceRequest->status,
        ];
    }
}
