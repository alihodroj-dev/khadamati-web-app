<?php

namespace App\Channels;

use App\Services\FirebaseNotificationService;
use Illuminate\Notifications\Notification;

class FcmChannel
{
    public function __construct(
        private readonly FirebaseNotificationService $firebase,
    ) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toFcm')) {
            return;
        }

        if (blank($notifiable->fcm_token)) {
            return;
        }

        $payload = $notification->toFcm($notifiable);

        if (empty($payload)) {
            return;
        }

        $this->firebase->sendToUser(
            $notifiable,
            $payload['title'],
            $payload['body'],
            $payload['data'] ?? [],
        );
    }
}
