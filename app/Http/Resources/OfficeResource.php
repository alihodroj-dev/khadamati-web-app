<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'municipality_id' => $this->municipality_id,
            'municipality' => new MunicipalityResource($this->whenLoaded('municipality')),
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'image_url' => $this->image_url,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'distance_km' => $this->when(
                $this->distance_km !== null,
                fn () => round((float) $this->distance_km, 2)
            ),
            'working_hours' => $this->working_hours ?? [],
            'services_count' => (int) ($this->services_count ?? 0),
            'average_rating' => $this->discoveryAverageRating(),
            'ratings_count' => (int) ($this->ratings_count ?? 0),
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    private function discoveryAverageRating(): ?float
    {
        $ratingsCount = (int) ($this->ratings_count ?? 0);

        if ($ratingsCount === 0 || $this->average_rating === null) {
            return null;
        }

        return round((float) $this->average_rating, 1);
    }
}
