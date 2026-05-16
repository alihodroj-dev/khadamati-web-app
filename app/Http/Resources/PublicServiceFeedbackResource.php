<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicServiceFeedbackResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'rating' => (int) $this->rating,
            'comment' => $this->comment,
            'created_at' => $this->created_at?->toISOString(),
            'citizen_name' => $this->citizenDisplayName(),
        ];
    }

    private function citizenDisplayName(): string
    {
        $name = $this->user?->name;

        if (! $name) {
            return 'Citizen';
        }

        $firstName = explode(' ', trim($name))[0];

        return $firstName !== '' ? $firstName : 'Citizen';
    }
}
