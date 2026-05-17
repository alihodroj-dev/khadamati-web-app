<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\RequestDocument;
use App\Notifications\DocumentUploadedNotification;
use Illuminate\Http\Request as HttpRequest;
use App\Models\ServiceRequest;
use App\Support\StaffOfficeScope;
use Illuminate\Support\Facades\Storage;

class StaffRequestController extends Controller
{
    /**
     * Show only assigned requests
     */
    public function index()
    {
        $requests = StaffOfficeScope::applyServiceRequestScope(
            ServiceRequest::with(['service', 'user', 'payment'])->latest(),
            auth()->user()
        )->paginate(10);

        return view('staff.requests.index', compact('requests'));
    }

    /**
     * Show single request
     */
    public function show($id)
    {
        $request = StaffOfficeScope::applyServiceRequestScope(
            ServiceRequest::with(['service', 'user', 'documents']),
            auth()->user()
        )->findOrFail($id);

        return view('staff.requests.show', compact('request'));
    }

    /**
     * Update status + staff notes
     */
    public function updateStatus(HttpRequest $httpRequest, $id)
    {
        $request = StaffOfficeScope::applyServiceRequestScope(
            ServiceRequest::query(),
            auth()->user()
        )->findOrFail($id);

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
        $request = StaffOfficeScope::applyServiceRequestScope(
            ServiceRequest::query(),
            auth()->user()
        )->findOrFail($id);

        $httpRequest->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'document_type' => 'required|string',
        ]);

        $file = $httpRequest->file('document');

        $path = $httpRequest->file('document')->store(
            'requests/' . $request->id . '/documents',
            'public'
        );

        $document = RequestDocument::create([
            'service_request_id' => $request->id,
            'uploaded_by' => auth()->id(),
            'source' => RequestDocument::SOURCE_STAFF,
            'purpose' => RequestDocument::PURPOSE_OFFICIAL_RESPONSE,
            'document_type' => $httpRequest->document_type,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'status' => 'approved',
            'rejection_reason' => null,
        ]);

        $request->loadMissing('user');

        $request->user?->notify(new DocumentUploadedNotification(
            $request,
            $document,
            'Official document available',
            sprintf(
                'An official document (%s) was added to your request %s.',
                $document->document_type,
                $request->reference_number
            )
        ));

        return back()->with('success', 'Document uploaded successfully');
    }
}
