<?php

namespace App\Notifications;

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
        // DEFERRED(roadmap): Add mail/sms channels + scheduled reminders; database only for now.
        // See docs/admin-office-roadmap.md#email--sms-appointment-reminders
        return ['database'];
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
            'url' => $notifiable->role === 'citizen' 
                ? route('citizen.appointments.show', $this->appointment->id)
                : route('staff.appointments.show', $this->appointment->id),
        ];
    }
}
