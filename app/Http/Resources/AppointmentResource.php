<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'status' => $this->status,

            'appointment_date' => $this->appointment_date,

            'appointment_time' => $this->appointment_time,

            'notes' => $this->notes,

            'service_request' => [
                'id' => $this->serviceRequest?->id,
                'tracking_number' => $this->serviceRequest?->tracking_number,
                'status' => $this->serviceRequest?->status,
            ],

            'citizen' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ],

            'staff' => $this->staff ? [
                'id' => $this->staff->id,
                'name' => $this->staff->name,
                'email' => $this->staff->email,
            ] : null,

            'created_at' => $this->created_at,
        ];
    }
}