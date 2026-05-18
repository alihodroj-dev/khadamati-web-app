<?php

namespace App\Notifications;

use App\Channels\FcmChannel;
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
                'type' => 'document_upload',
                'service_request_id' => (string) $this->serviceRequest->id,
                'reference_number' => (string) $this->serviceRequest->reference_number,
                'document_type' => (string) ($this->document?->document_type ?? ''),
            ],
        ];
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
