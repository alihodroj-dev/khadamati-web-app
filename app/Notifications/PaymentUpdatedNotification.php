<?php

namespace App\Notifications;

use App\Channels\FcmChannel;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Payment $payment,
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
                'type' => 'payment_update',
                'payment_id' => (string) $this->payment->id,
                'service_request_id' => (string) $this->payment->service_request_id,
                'status' => (string) $this->payment->status,
            ],
        ];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'payment_update',
            'title' => $this->title,
            'body' => $this->body,
            'payment_id' => $this->payment->id,
            'service_request_id' => $this->payment->service_request_id,
            'status' => $this->payment->status,
            'amount' => (float) $this->payment->amount,
            'currency' => $this->payment->currency,
            'transaction_reference' => $this->payment->transaction_reference,
        ];
    }
}
