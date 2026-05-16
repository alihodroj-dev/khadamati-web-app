<?php

namespace App\Http\Resources;

use App\Models\RequestDocument;
use App\Support\RequiredDocumentDefinition;
use App\Support\ServiceRequestTrackingUrls;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_number' => $this->reference_number,
            ...ServiceRequestTrackingUrls::for($this->resource),
            'status' => $this->status,

            'user_id' => $this->user_id,
            'service_id' => $this->service_id,
            'office_id' => $this->office_id,
            'assigned_staff_id' => $this->assigned_staff_id,

            'service' => new ServiceResource($this->whenLoaded('service')),
            'office' => new OfficeResource($this->whenLoaded('office')),
            'citizen' => $this->whenLoaded('user'),
            'assigned_staff' => $this->whenLoaded('assignedStaff'),
            'documents' => RequestDocumentResource::collection($this->whenLoaded('documents')),
            'requirement_documents' => $this->when(
                $this->relationLoaded('documents'),
                fn () => RequestDocumentResource::collection(
                    $this->documents
                        ->where('purpose', RequestDocument::PURPOSE_REQUIREMENT)
                        ->values()
                )
            ),
            'official_documents' => $this->when(
                $this->relationLoaded('documents'),
                fn () => RequestDocumentResource::collection(
                    $this->documents
                        ->filter(fn (RequestDocument $document) => $document->isOfficialOutput())
                        ->values()
                )
            ),
            'required_documents' => $this->when(
                $this->relationLoaded('service'),
                fn () => RequiredDocumentDefinition::normalizeList(
                    $this->service->required_documents ?? []
                )
            ),
            'missing_documents' => $this->when(
                $this->relationLoaded('service') && $this->relationLoaded('documents'),
                fn () => $this->missingRequiredDocuments()
            ),
            'appointment' => new AppointmentResource($this->whenLoaded('appointment')),
            'payment' => new PaymentResource($this->whenLoaded('payment')),
            'feedback' => new FeedbackResource($this->whenLoaded('feedback')),

            'citizen_notes' => $this->citizen_notes,
            'staff_notes' => $this->staff_notes,
            'rejection_reason' => $this->rejection_reason,
            'submitted_data' => $this->submitted_data ?? [],

            'submitted_at' => $this->submitted_at?->toISOString(),
            'reviewed_at' => $this->reviewed_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
