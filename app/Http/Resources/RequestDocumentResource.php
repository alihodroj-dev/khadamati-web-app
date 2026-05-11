<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class RequestDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_request_id' => $this->service_request_id,
            'uploaded_by' => $this->uploaded_by,

            'document_type' => $this->document_type,
            'file_name' => $this->file_name,
            'file_path' => $this->file_path,
            'file_url' => Storage::disk('public')->url($this->file_path),

            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'file_size_kb' => $this->file_size ? round($this->file_size / 1024, 2) : null,

            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
