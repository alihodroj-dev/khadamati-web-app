<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RequestDocumentResource;
use App\Models\RequestDocument;
use App\Models\ServiceRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RequestDocumentController extends Controller
{
    use ApiResponse;

    public function index(Request $request, ServiceRequest $serviceRequest)
    {
        if ($serviceRequest->user_id !== $request->user()->id) {
            return $this->errorResponse(
                'You are not allowed to view documents for this service request.',
                null,
                403
            );
        }

        $documents = $serviceRequest
            ->documents()
            ->latest()
            ->get();

        return $this->successResponse(
            [
                'documents' => RequestDocumentResource::collection($documents),
            ],
            'Request documents retrieved successfully'
        );
    }

    public function store(Request $request, ServiceRequest $serviceRequest)
    {
        if ($serviceRequest->user_id !== $request->user()->id) {
            return $this->errorResponse(
                'You are not allowed to upload documents for this service request.',
                null,
                403
            );
        }

        if (in_array($serviceRequest->status, ['completed', 'cancelled', 'rejected'])) {
            return $this->errorResponse(
                'Documents cannot be uploaded for this service request status.',
                null,
                422
            );
        }

        $validated = $request->validate([
            'document_type' => ['required', 'string', 'max:255'],
            'document' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
        ]);

        $file = $request->file('document');

        $path = $file->store(
            'request-documents/'.$serviceRequest->id,
            'public'
        );

        $document = RequestDocument::create([
            'service_request_id' => $serviceRequest->id,
            'uploaded_by' => $request->user()->id,
            'document_type' => $validated['document_type'],
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'status' => 'pending',
        ]);

        return $this->successResponse(
            [
                'document' => new RequestDocumentResource($document),
            ],
            'Document uploaded successfully',
            201
        );
    }

    public function destroy(Request $request, ServiceRequest $serviceRequest, RequestDocument $document)
    {
        if ($serviceRequest->user_id !== $request->user()->id) {
            return $this->errorResponse(
                'You are not allowed to delete documents for this service request.',
                null,
                403
            );
        }

        if ($document->service_request_id !== $serviceRequest->id) {
            return $this->errorResponse(
                'Document not found for this service request.',
                null,
                404
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

        return $this->successResponse(
            null,
            'Document deleted successfully'
        );
    }
}
