<?php

namespace App\Notifications;

use App\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AccountStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly bool $isActive,
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
            'title' => $this->isActive ? 'Account Activated' : 'Account Deactivated',
            'body' => $this->isActive
                ? 'Your Khadamati account is now active.'
                : 'Your Khadamati account has been deactivated.',
            'data' => [
                'type' => 'account_status',
                'is_active' => $this->isActive ? 'true' : 'false',
            ],
        ];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'account_status',
            'title' => $this->isActive ? 'Account Activated' : 'Account Deactivated',
            'body' => $this->isActive
                ? 'Your Khadamati account is now active.'
                : 'Your Khadamati account has been deactivated.',
            'is_active' => $this->isActive,
        ];
    }
}
