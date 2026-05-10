<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_number' => $this->reference_number,
            'status' => $this->status,

            'user_id' => $this->user_id,
            'service_id' => $this->service_id,
            'assigned_staff_id' => $this->assigned_staff_id,

            'service' => new ServiceResource($this->whenLoaded('service')),
            'citizen' => $this->whenLoaded('user'),
            'assigned_staff' => $this->whenLoaded('assignedStaff'),

            'citizen_notes' => $this->citizen_notes,
            'staff_notes' => $this->staff_notes,
            'rejection_reason' => $this->rejection_reason,
            'submitted_data' => $this->submitted_data ?? [],

            'submitted_at' => $this->submitted_at?->toISOString(),
            'reviewed_at' => $this->reviewed_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
