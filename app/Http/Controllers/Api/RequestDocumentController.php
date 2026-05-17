<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RequestDocumentResource;
use App\Models\RequestDocument;
use Illuminate\Support\Collection;
use App\Models\ServiceRequest;
use App\Notifications\DocumentUploadedNotification;
use App\Support\RequiredDocumentDefinition;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RequestDocumentController extends Controller
{
    use ApiResponse;

    public function index(Request $request, ServiceRequest $serviceRequest)
    {
        if ($response = $this->denyUnlessOwner($request, $serviceRequest, 'view')) {
            return $response;
        }

        $documents = $serviceRequest
            ->documents()
            ->latest()
            ->get();

        return $this->successResponse(
            $this->documentCollectionPayload($documents),
            'Request documents retrieved successfully'
        );
    }

    public function store(Request $request, ServiceRequest $serviceRequest)
    {
        if ($response = $this->denyUnlessCanUpload($request, $serviceRequest)) {
            return $response;
        }

        $requiredDefinitions = $this->requiredDocumentDefinitions($serviceRequest);

        $validated = $request->validate([
            'document_type' => ['required', 'string', 'max:255'],
            'document' => ['required', 'file'],
        ]);

        if ($response = $this->validateDocumentItem(
            $validated['document_type'],
            $request->file('document'),
            $requiredDefinitions,
            'document_type',
            'document'
        )) {
            return $response;
        }

        $document = $this->createRequestDocument(
            $request,
            $serviceRequest,
            $validated['document_type'],
            $request->file('document'),
            $requiredDefinitions
        );

        $this->notifyAssignedStaffOfCitizenUpload(
            $serviceRequest,
            $document,
            'Document uploaded',
            sprintf(
                'A required document (%s) was uploaded for request %s.',
                $document->document_type,
                $serviceRequest->reference_number
            )
        );

        return $this->successResponse(
            [
                'document' => new RequestDocumentResource($document),
            ],
            'Document uploaded successfully',
            201
        );
    }

    public function bulkStore(Request $request, ServiceRequest $serviceRequest)
    {
        if ($response = $this->denyUnlessCanUpload($request, $serviceRequest)) {
            return $response;
        }

        $requiredDefinitions = $this->requiredDocumentDefinitions($serviceRequest);

        $validated = $request->validate([
            'documents' => ['required', 'array', 'min:1'],
            'documents.*.document_type' => ['required', 'string', 'max:255'],
            'documents.*.file' => ['required', 'file'],
        ]);

        $prepared = [];

        foreach ($validated['documents'] as $index => $item) {
            if ($response = $this->validateDocumentItem(
                $item['document_type'],
                $item['file'],
                $requiredDefinitions,
                "documents.{$index}.document_type",
                "documents.{$index}.file"
            )) {
                return $response;
            }

            $prepared[] = [
                'document_type' => $item['document_type'],
                'file' => $item['file'],
            ];
        }

        $documents = [];

        foreach ($prepared as $item) {
            $documents[] = $this->createRequestDocument(
                $request,
                $serviceRequest,
                $item['document_type'],
                $item['file'],
                $requiredDefinitions
            );
        }

        $count = count($documents);

        $this->notifyAssignedStaffOfCitizenUpload(
            $serviceRequest,
            $documents[0] ?? null,
            'Documents uploaded',
            sprintf(
                '%d required document%s uploaded for request %s.',
                $count,
                $count === 1 ? '' : 's',
                $serviceRequest->reference_number
            )
        );

        return $this->successResponse(
            [
                'documents' => RequestDocumentResource::collection(collect($documents)),
            ],
            'Documents uploaded successfully',
            201
        );
    }

    public function download(Request $request, ServiceRequest $serviceRequest, RequestDocument $document)
    {
        if ($response = $this->denyUnlessOwner($request, $serviceRequest, 'download')) {
            return $response;
        }

        if ($document->service_request_id !== $serviceRequest->id) {
            return $this->errorResponse(
                'Document not found for this service request.',
                null,
                404
            );
        }

        if (! $document->file_path || ! Storage::disk('public')->exists($document->file_path)) {
            return $this->errorResponse(
                'Document file not found.',
                null,
                404
            );
        }

        return Storage::disk('public')->download(
            $document->file_path,
            $document->file_name,
            ['Content-Type' => $document->mime_type ?? 'application/octet-stream']
        );
    }

    public function destroy(Request $request, ServiceRequest $serviceRequest, RequestDocument $document)
    {
        if ($response = $this->denyUnlessOwner($request, $serviceRequest, 'delete')) {
            return $response;
        }

        if ($document->service_request_id !== $serviceRequest->id) {
            return $this->errorResponse(
                'Document not found for this service request.',
                null,
                404
            );
        }

        if (! $document->isCitizenRequirement()) {
            return $this->errorResponse(
                'Only citizen requirement uploads can be deleted.',
                null,
                422
            );
        }

        if ($document->status === 'approved') {
            return $this->errorResponse(
                'Approved documents cannot be deleted.',
                null,
                422
            );
        }

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return $this->successResponse(null, 'Document deleted successfully');
    }

    private function denyUnlessOwner(Request $request, ServiceRequest $serviceRequest, string $action): ?JsonResponse
    {
        if ($serviceRequest->user_id !== $request->user()->id) {
            return $this->errorResponse(
                "You are not allowed to {$action} documents for this service request.",
                null,
                403
            );
        }

        return null;
    }

    private function denyUnlessCanUpload(Request $request, ServiceRequest $serviceRequest): ?JsonResponse
    {
        if ($response = $this->denyUnlessOwner($request, $serviceRequest, 'upload')) {
            return $response;
        }

        if (in_array($serviceRequest->status, ['completed', 'cancelled', 'rejected'], true)) {
            return $this->errorResponse(
                'Documents cannot be uploaded for this service request status.',
                null,
                422
            );
        }

        return null;
    }

    /**
     * @return list<array{
     *     key: string,
     *     label: string,
     *     required: bool,
     *     accepted_types: list<string>,
     *     max_size_mb: int
     * }>
     */
    private function requiredDocumentDefinitions(ServiceRequest $serviceRequest): array
    {
        $serviceRequest->loadMissing('service');

        return RequiredDocumentDefinition::normalizeList(
            $serviceRequest->service?->required_documents ?? []
        );
    }

    private function validateDocumentItem(
        string $documentType,
        UploadedFile $file,
        array $requiredDefinitions,
        string $typeField,
        string $fileField
    ): ?JsonResponse {
        $resolvedKey = RequiredDocumentDefinition::resolveTypeKey(
            $documentType,
            $requiredDefinitions
        );

        if ($requiredDefinitions !== [] && $resolvedKey === null) {
            return $this->errorResponse(
                'The document type is not required for this service.',
                [
                    $typeField => ['The document type must match a required document key or label.'],
                ],
                422
            );
        }

        $definition = $resolvedKey !== null
            ? RequiredDocumentDefinition::find($resolvedKey, $requiredDefinitions)
            : null;

        $acceptedTypes = $definition['accepted_types'] ?? RequiredDocumentDefinition::DEFAULT_ACCEPTED_TYPES;
        $maxSizeKb = ($definition['max_size_mb'] ?? RequiredDocumentDefinition::DEFAULT_MAX_SIZE_MB) * 1024;

        $validator = Validator::make(
            [$fileField => $file],
            [
                $fileField => [
                    'file',
                    'mimes:'.implode(',', $acceptedTypes),
                    'max:'.$maxSizeKb,
                ],
            ]
        );

        if ($validator->fails()) {
            return $this->errorResponse(
                'Validation failed.',
                $validator->errors()->toArray(),
                422
            );
        }

        return null;
    }

    /**
     * @param  list<array{
     *     key: string,
     *     label: string,
     *     required: bool,
     *     accepted_types: list<string>,
     *     max_size_mb: int
     * }>  $requiredDefinitions
     */
    private function createRequestDocument(
        Request $request,
        ServiceRequest $serviceRequest,
        string $documentType,
        UploadedFile $file,
        array $requiredDefinitions
    ): RequestDocument {
        $resolvedKey = RequiredDocumentDefinition::resolveTypeKey(
            $documentType,
            $requiredDefinitions
        );

        $path = $file->store(
            'request-documents/'.$serviceRequest->id,
            'public'
        );

        return RequestDocument::create([
            'service_request_id' => $serviceRequest->id,
            'uploaded_by' => $request->user()->id,
            'source' => RequestDocument::SOURCE_CITIZEN,
            'purpose' => RequestDocument::PURPOSE_REQUIREMENT,
            'document_type' => $resolvedKey ?? $documentType,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'status' => 'pending',
        ]);
    }

    // DEFERRED(roadmap): Pair with broadcast for live staff alerts on citizen upload.
    // See docs/admin-office-roadmap.md#live-real-time-notifications
    private function notifyAssignedStaffOfCitizenUpload(
        ServiceRequest $serviceRequest,
        ?RequestDocument $document,
        string $title,
        string $body
    ): void {
        $serviceRequest->loadMissing('assignedStaff');

        if ($serviceRequest->assignedStaff === null) {
            return;
        }

        $serviceRequest->assignedStaff->notify(
            new DocumentUploadedNotification($serviceRequest, $document, $title, $body)
        );
    }

    /**
     * @param  Collection<int, RequestDocument>  $documents
     * @return array<string, mixed>
     */
    private function documentCollectionPayload(Collection $documents): array
    {
        $requirementDocuments = $documents
            ->where('purpose', RequestDocument::PURPOSE_REQUIREMENT)
            ->values();

        $officialDocuments = $documents
            ->filter(fn (RequestDocument $document) => $document->isOfficialOutput())
            ->values();

        return [
            'documents' => RequestDocumentResource::collection($documents),
            'requirement_documents' => RequestDocumentResource::collection($requirementDocuments),
            'official_documents' => RequestDocumentResource::collection($officialDocuments),
        ];
    }
}
