<?php

namespace App\Http\Resources;

use App\Support\CitizenDisplayName;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicFeedbackResponseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'message' => $this->message,
            'created_at' => $this->created_at?->toISOString(),
            'responder_display_name' => $this->responder
                ? CitizenDisplayName::fromUser($this->responder)
                : 'Office',
        ];
    }
}
