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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'father_name' => $this->father_name,
            'mother_name' => $this->mother_name,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'email' => $this->email,
            'phone' => $this->phone,
            'national_id' => $this->national_id,
            'id_document_path' => $this->id_document_path,
            'id_document_url' => $this->id_document_path
                ? Storage::disk('public')->url($this->id_document_path)
                : null,
            'id_front_path' => $this->id_front_path,
            'id_front_url' => $this->id_front_path
                ? Storage::disk('public')->url($this->id_front_path)
                : null,
            'id_back_path' => $this->id_back_path,
            'id_back_url' => $this->id_back_path
                ? Storage::disk('public')->url($this->id_back_path)
                : null,
            'role' => $this->role,
            'office_id' => $this->office_id,
            'office' => new OfficeResource($this->whenLoaded('office')),
            'is_active' => (bool) $this->is_active,
            'profile_completed' => (bool) $this->profile_completed,
            'has_fcm_token' => filled($this->fcm_token),
            'push_notifications_enabled' => (bool) $this->push_notifications_enabled,
            'email_notifications_enabled' => (bool) $this->email_notifications_enabled,
            'sms_notifications_enabled' => (bool) $this->sms_notifications_enabled,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
