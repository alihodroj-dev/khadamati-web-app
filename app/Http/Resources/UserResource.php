<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'national_id' => $this->national_id,
            'id_document_path' => $this->id_document_path,
            'id_document_url' => $this->id_document_path
                ? Storage::disk('public')->url($this->id_document_path)
                : null,
            'role' => $this->role,
            'is_active' => (bool) $this->is_active,
            'push_notifications_enabled' => (bool) $this->push_notifications_enabled,
            'email_notifications_enabled' => (bool) $this->email_notifications_enabled,
            'sms_notifications_enabled' => (bool) $this->sms_notifications_enabled,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
