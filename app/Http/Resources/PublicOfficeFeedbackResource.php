<?php

namespace App\Http\Resources;

use App\Support\CitizenDisplayName;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicOfficeFeedbackResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'rating' => (int) $this->rating,
            'comment' => $this->comment,
            'created_at' => $this->created_at?->toISOString(),
            'citizen_name' => CitizenDisplayName::fromUser($this->user),
            'service_name' => $this->serviceRequest?->service?->name,
            'responses' => PublicFeedbackResponseResource::collection($this->whenLoaded('responses')),
        ];
    }
}
