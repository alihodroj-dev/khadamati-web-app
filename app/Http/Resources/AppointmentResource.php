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
            'appointment_date' => $this->appointment_date?->format('Y-m-d'),
            'appointment_time' => $this->formatAppointmentTime(),
            'notes' => $this->notes,
            'service_request' => $this->serviceRequest ? [
                'id' => $this->serviceRequest->id,
                'reference_number' => $this->serviceRequest->reference_number,
                'tracking_token' => $this->serviceRequest->tracking_token,
                'status' => $this->serviceRequest->status,
            ] : null,
            'office' => $this->officeSummary(),
            'service' => $this->serviceSummary(),
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
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    protected function formatAppointmentTime(): ?string
    {
        if ($this->appointment_time === null) {
            return null;
        }

        $time = (string) $this->appointment_time;

        return strlen($time) >= 5 ? substr($time, 0, 5) : $time;
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     address: string|null,
     *     latitude: float|null,
     *     longitude: float|null
     * }|null
     */
    protected function officeSummary(): ?array
    {
        $office = $this->serviceRequest?->office;

        if ($office === null) {
            return null;
        }

        return [
            'id' => $office->id,
            'name' => $office->name,
            'address' => $office->address,
            'latitude' => $office->latitude !== null ? (float) $office->latitude : null,
            'longitude' => $office->longitude !== null ? (float) $office->longitude : null,
        ];
    }

    /**
     * @return array{id: int, name: string}|null
     */
    protected function serviceSummary(): ?array
    {
        $service = $this->serviceRequest?->service;

        if ($service === null) {
            return null;
        }

        return [
            'id' => $service->id,
            'name' => $service->name,
        ];
    }
}