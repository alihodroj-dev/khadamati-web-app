<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\RequestDocument;
use App\Support\RequestDocumentPurposeResolver;
use Illuminate\Http\Request as HttpRequest;
use App\Models\ServiceRequest;

use Illuminate\Support\Facades\Storage;

class StaffRequestController extends Controller
{
    /**
     * Show only assigned requests
     */
    public function index()
    {
        $requests = ServiceRequest::with(['service', 'user', 'payment'])
            ->where('assigned_staff_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('staff.requests.index', compact('requests'));
    }

    /**
     * Show single request
     */
    public function show($id)
    {
        $request = ServiceRequest::with(['service', 'user', 'documents'])
            ->where('assigned_staff_id', auth()->id())
            ->findOrFail($id);

        return view('staff.requests.show', compact('request'));
    }

    /**
     * Update status + staff notes
     */
    public function updateStatus(HttpRequest $httpRequest, $id)
    {
        $request = ServiceRequest::where('assigned_staff_id', auth()->id())
            ->findOrFail($id);

        $request->status = $httpRequest->status;
        $request->staff_notes = $httpRequest->staff_notes;

        if ($httpRequest->status === 'completed') {
            $request->completed_at = now();

            Payment::firstOrCreate(
                ['service_request_id' => $request->id],
                [
                    'user_id'               => $request->user_id,
                    'amount'                => $request->service->base_fee,
                    'currency'              => 'LBP',
                    'payment_method'        => null,
                    'status'                => 'pending',
                    'transaction_reference' => uniqid('PAY-'),
                ]
            );
        }

        $request->save();

        return back()->with('success', 'Request updated successfully');
    }

    public function uploadDocument(HttpRequest $httpRequest, $id)
    {
        $request = ServiceRequest::where('assigned_staff_id', auth()->id())
            ->findOrFail($id);

        $httpRequest->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'document_type' => 'required|string',
        ]);

        $file = $httpRequest->file('document');

        $path = $httpRequest->file('document')->store(
            'requests/' . $request->id . '/documents',
            'public'
        );

        RequestDocument::create([
            'service_request_id' => $request->id,
            'uploaded_by' => auth()->id(),
            'source' => RequestDocument::SOURCE_STAFF,
            'purpose' => RequestDocumentPurposeResolver::fromStaffDocumentType(
                $httpRequest->document_type
            ),
            'document_type' => $httpRequest->document_type,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'status' => 'approved',
            'rejection_reason' => null,
        ]);

        return back()->with('success', 'Document uploaded successfully');
    }
}
