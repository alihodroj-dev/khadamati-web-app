<?php

namespace App\Notifications;

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
        // DEFERRED(roadmap): Optional broadcast channel for live staff navbar updates.
        // See docs/admin-office-roadmap.md#live-real-time-notifications
        return ['database'];
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
