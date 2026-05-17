<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficeTimeSlotBlockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'office_id' => $this->office_id,
            'staff_id' => $this->staff_id,
            'date' => $this->date?->format('Y-m-d'),
            'start_time' => $this->formatTime($this->start_time),
            'end_time' => $this->formatTime($this->end_time),
            'reason' => $this->reason,
            'staff' => new UserResource($this->whenLoaded('staff')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    private function formatTime(mixed $time): string
    {
        return Carbon::parse($time)->format('H:i');
    }
}
