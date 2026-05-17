<?php

namespace App\Support;

class NotificationPresentation
{
    /**
     * @param  array<string, mixed>  $data
     * @return array{
     *     type: string,
     *     icon: string,
     *     deep_link: null|array{type: string, id: int}
     * }
     */
    public function present(array $data): array
    {
        $type = is_string($data['type'] ?? null) ? $data['type'] : 'general';

        return [
            'type' => $type,
            'icon' => $this->iconFor($type),
            'deep_link' => $this->deepLinkFor($type, $data),
        ];
    }

    private function iconFor(string $type): string
    {
        return match ($type) {
            'request_update' => 'request',
            'payment_update' => 'payment',
            'appointment_update' => 'appointment',
            default => 'bell',
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return null|array{type: string, id: int}
     */
    private function deepLinkFor(string $type, array $data): ?array
    {
        return match ($type) {
            'request_update' => $this->link('service_request', $data['service_request_id'] ?? null),
            'payment_update' => $this->link('payment', $data['payment_id'] ?? null),
            'appointment_update' => $this->link('appointment', $data['appointment_id'] ?? null),
            default => null,
        };
    }

    /**
     * @return null|array{type: string, id: int}
     */
    private function link(string $type, mixed $id): ?array
    {
        if ($id === null || $id === '') {
            return null;
        }

        return [
            'type' => $type,
            'id' => (int) $id,
        ];
    }
}
