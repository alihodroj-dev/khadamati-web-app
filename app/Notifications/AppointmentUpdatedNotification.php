<?php

namespace App\Notifications;

use App\Channels\FcmChannel;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppointmentUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Appointment $appointment,
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
                'type' => 'appointment_update',
                'appointment_id' => (string) $this->appointment->id,
                'service_request_id' => (string) $this->appointment->service_request_id,
                'status' => (string) $this->appointment->status,
                'appointment_date' => $this->appointment->getRawOriginal('appointment_date') ?? '',
            ],
        ];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'appointment_update',
            'title' => $this->title,
            'body' => $this->body,
            'appointment_id' => $this->appointment->id,
            'service_request_id' => $this->appointment->service_request_id,
            'status' => $this->appointment->status,
            'appointment_date' => $this->appointment->appointment_date?->toDateString(),
            'appointment_time' => $this->appointment->appointment_time,
        ];
    }
}
