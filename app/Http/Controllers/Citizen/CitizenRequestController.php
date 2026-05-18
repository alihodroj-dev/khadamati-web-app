<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\RequestDocument;
use App\Services\ServiceRequestQrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CitizenRequestController extends Controller
{
    public function __construct(
        private ServiceRequestQrCodeService $qrCodeService
    ) {}

    public function index()
    {
        $requests = ServiceRequest::where('user_id', auth()->id())
            ->with(['service', 'office', 'payment'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('citizen.requests.index', compact('requests'));
    }

    public function show($id)
    {
        $request = ServiceRequest::where('user_id', auth()->id())
            ->with(['service', 'office', 'documents', 'payment', 'appointment', 'feedback'])
            ->findOrFail($id);

        $qrCodeUrl = $this->qrCodeService->publicUrl($request->qr_code_path);
        $trackingUrl = route('tracking.show', $request->tracking_token);

        return view('citizen.requests.show', compact('request', 'qrCodeUrl', 'trackingUrl'));
    }

    public function cancel($id)
    {
        $request = ServiceRequest::where('user_id', auth()->id())
            ->whereIn('status', ['pending', 'under_review'])
            ->findOrFail($id);

        $request->update([
            'status' => 'cancelled',
            'completed_at' => now(),
        ]);

        return redirect()->route('citizen.requests.index')
            ->with('success', 'Request cancelled successfully.');
    }

    public function downloadDocument($requestId, $documentId)
    {
        $serviceRequest = ServiceRequest::where('user_id', auth()->id())->findOrFail($requestId);
        $document = RequestDocument::where('service_request_id', $serviceRequest->id)
            ->where('id', $documentId)
            ->firstOrFail();

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }
}