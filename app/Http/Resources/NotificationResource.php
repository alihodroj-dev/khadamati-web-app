<?php

namespace App\Http\Resources;

use App\Support\NotificationPresentation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = is_array($this->data) ? $this->data : [];
        $presentation = app(NotificationPresentation::class)->present($data);

        return [
            'id' => $this->id,
            'type' => $presentation['type'],
            'title' => $data['title'] ?? null,
            'body' => $data['body'] ?? null,
            'icon' => $presentation['icon'],
            'deep_link' => $presentation['deep_link'],
            'read_at' => $this->read_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
