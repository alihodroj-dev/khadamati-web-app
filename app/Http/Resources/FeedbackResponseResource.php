<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedbackResponseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'feedback_id' => $this->feedback_id,
            'responder_id' => $this->responder_id,
            'visibility' => $this->visibility,
            'message' => $this->message,
            'responder' => new UserResource($this->whenLoaded('responder')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
