<?php

namespace App\Notifications;

use App\Models\RequestDocument;
use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentUploadedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ServiceRequest $serviceRequest,
        private readonly ?RequestDocument $document,
        private readonly string $title,
        private readonly string $body
    ) {}

    public function via(object $notifiable): array
    {
        // DEFERRED(roadmap): Optional broadcast channel when citizens upload documents.
        // See docs/admin-office-roadmap.md#live-real-time-notifications
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'document_upload',
            'title' => $this->title,
            'body' => $this->body,
            'service_request_id' => $this->serviceRequest->id,
            'request_document_id' => $this->document?->id,
            'document_type' => $this->document?->document_type,
            'reference_number' => $this->serviceRequest->reference_number,
            'status' => $this->serviceRequest->status,
        ];
    }
}
