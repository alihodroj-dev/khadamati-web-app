<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_category_id' => $this->service_category_id,
            'office_id' => $this->office_id,
            'category' => new ServiceCategoryResource($this->whenLoaded('category')),
            'office' => new OfficeResource($this->whenLoaded('office')),
            'name' => $this->name,
            'description' => $this->description,
            'base_fee' => (float) $this->base_fee,
            'estimated_processing_days' => $this->estimated_processing_days,
            'required_documents' => $this->required_documents ?? [],
            'requires_appointment' => (bool) $this->requires_appointment,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
