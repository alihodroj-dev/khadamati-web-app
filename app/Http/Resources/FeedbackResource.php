<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedbackResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_request_id' => $this->service_request_id,
            'user_id' => $this->user_id,
            'rating' => (int) $this->rating,
            'comment' => $this->comment,
            'service_request' => new ServiceRequestResource($this->whenLoaded('serviceRequest')),
            'user' => new UserResource($this->whenLoaded('user')),
            'responses' => FeedbackResponseResource::collection($this->whenLoaded('responses')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
