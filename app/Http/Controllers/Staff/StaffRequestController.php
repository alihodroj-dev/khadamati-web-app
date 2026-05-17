<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\RequestDocument;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\DocumentUploadedNotification;
use App\Support\RequestDocumentPurposeResolver;
use App\Support\ServiceRequestAssignment;
use App\Support\ServiceRequestStatus;
use App\Support\ServiceRequestStatusUpdater;
use App\Support\StaffOfficeScope;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class StaffRequestController extends Controller
{
    public function index(HttpRequest $httpRequest)
    {
        $query = StaffOfficeScope::applyServiceRequestScope(
            ServiceRequest::with(['service', 'user', 'payment', 'assignedStaff'])->latest(),
            auth()->user()
        );

        if ($httpRequest->filled('status')) {
            $query->where('status', $httpRequest->status);
        }

        if ($httpRequest->assignment === 'assigned') {
            $query->whereNotNull('assigned_staff_id');
        } elseif ($httpRequest->assignment === 'unassigned') {
            $query->whereNull('assigned_staff_id');
        }

        $requests = $query->paginate(10)->withQueryString();

        return view('staff.requests.index', compact('requests'));
    }

    public function show($id)
    {
        $request = StaffOfficeScope::applyServiceRequestScope(
            ServiceRequest::with(['service', 'user', 'documents', 'assignedStaff', 'office']),
            auth()->user()
        )->findOrFail($id);

        $officeStaff = User::query()
            ->where('role', User::ROLE_STAFF)
            ->where('office_id', auth()->user()->office_id)
            ->orderBy('name')
            ->get();

        return view('staff.requests.show', compact('request', 'officeStaff'));
    }

    public function updateStatus(HttpRequest $httpRequest, $id)
    {
        $request = StaffOfficeScope::applyServiceRequestScope(
            ServiceRequest::query(),
            auth()->user()
        )->findOrFail($id);

        $httpRequest->validate([
            'status' => ['required', ServiceRequestStatus::validationRule(ServiceRequestStatus::staffUpdatable())],
            'staff_notes' => ['nullable', 'string'],
            'rejection_reason' => ['required_if:status,'.ServiceRequestStatus::REJECTED, 'nullable', 'string'],
        ]);

        ServiceRequestStatusUpdater::apply(
            $request,
            $httpRequest->status,
            $httpRequest->staff_notes,
            $httpRequest->rejection_reason
        );

        return back()->with('success', 'Request updated successfully');
    }

    public function assignStaff(HttpRequest $httpRequest, $id)
    {
        $request = StaffOfficeScope::applyServiceRequestScope(
            ServiceRequest::query(),
            auth()->user()
        )->findOrFail($id);

        $httpRequest->validate([
            'staff_id' => ['required', 'exists:users,id'],
        ]);

        $staffUser = User::findOrFail($httpRequest->staff_id);

        try {
            ServiceRequestAssignment::assign($request, $staffUser);
        } catch (ValidationException $exception) {
            return back()
                ->withInput()
                ->with('error', collect($exception->errors())->flatten()->first());
        }

        return back()->with('success', 'Staff assigned successfully');
    }

    public function uploadDocument(HttpRequest $httpRequest, $id)
    {
        $request = StaffOfficeScope::applyServiceRequestScope(
            ServiceRequest::query(),
            auth()->user()
        )->findOrFail($id);

        $httpRequest->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'document_type' => ['required', 'string', 'in:response,certificate,receipt,approval,rejection,other'],
        ]);

        $file = $httpRequest->file('document');
        $documentType = $httpRequest->document_type;

        $path = $file->store(
            'requests/'.$request->id.'/documents',
            'public'
        );

        $document = RequestDocument::create([
            'service_request_id' => $request->id,
            'uploaded_by' => auth()->id(),
            'source' => RequestDocument::SOURCE_STAFF,
            'purpose' => RequestDocumentPurposeResolver::fromStaffDocumentType($documentType),
            'document_type' => $documentType,
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
