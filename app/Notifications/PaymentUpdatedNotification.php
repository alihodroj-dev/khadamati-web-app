<?php

namespace App\Notifications;

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
        return ['database'];
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
