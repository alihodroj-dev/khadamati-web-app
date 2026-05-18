<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\RequestUpdatedNotification;
use App\Support\ServiceRequestAssignment;
use App\Support\ServiceRequestStatus;
use App\Support\ServiceRequestStatusUpdater;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Validation\ValidationException;

class ServiceRequestController extends Controller
{
    public function index(HttpRequest $request)
    {
        $query = ServiceRequest::with(['user', 'service', 'assignedStaff', 'office']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->assignment === 'assigned') {
            $query->whereNotNull('assigned_staff_id');
        } elseif ($request->assignment === 'unassigned') {
            $query->whereNull('assigned_staff_id');
        }

        $requests = $query->latest()->paginate(10)->withQueryString();

        return view('admin.requests.index', compact('requests'));
    }

    public function show($id)
    {
        $request = ServiceRequest::with(['user', 'service', 'assignedStaff', 'office'])
            ->findOrFail($id);

        $staff = User::query()
            ->where('role', User::ROLE_STAFF)
            ->when(
                $request->office_id,
                fn ($query) => $query->where('office_id', $request->office_id)
            )
            ->orderBy('name')
            ->get();

        return view('admin.requests.show', compact('request', 'staff'));
    }

    public function updateStatus(HttpRequest $httpRequest, $id)
    {
        $httpRequest->validate([
            'status' => ['required', ServiceRequestStatus::validationRule(ServiceRequestStatus::adminUpdatable())],
            'rejection_reason' => ['required_if:status,'.ServiceRequestStatus::REJECTED, 'nullable', 'string'],
        ]);

        $request = ServiceRequest::findOrFail($id);

        ServiceRequestStatusUpdater::apply(
            $request,
            $httpRequest->status,
            null,
            $httpRequest->rejection_reason
        );

        // SEND NOTIFICATION TO CITIZEN
        $citizen = $request->user;
        $citizen->notify(new RequestUpdatedNotification(
            $request,
            'Request Status Updated',
            'Your request #' . $request->reference_number . ' is now ' . ucfirst(str_replace('_', ' ', $httpRequest->status))
        ));

        return back()->with('success', 'Status updated');
    }

    public function assignStaff(HttpRequest $httpRequest, $id)
    {
        $httpRequest->validate([
            'staff_id' => ['required', 'exists:users,id'],
        ]);

        $request = ServiceRequest::findOrFail($id);
        $staffUser = User::findOrFail($httpRequest->staff_id);

        try {
            ServiceRequestAssignment::assign($request, $staffUser);
            
            // NOTIFY STAFF
            $staffUser->notify(new RequestUpdatedNotification(
                $request,
                'New Request Assigned',
                'You have been assigned to request #' . $request->reference_number
            ));
            
            //  NOTIFY CITIZEN
            $citizen = $request->user;
            $citizen->notify(new RequestUpdatedNotification(
                $request,
                'Staff Assigned',
                'A staff member has been assigned to your request #' . $request->reference_number
            ));
            
        } catch (ValidationException $exception) {
            return back()
                ->withInput()
                ->with('error', collect($exception->errors())->flatten()->first());
        }

        return back()->with('success', 'Staff assigned successfully');
    }

    public function destroy($id)
    {
        $request = ServiceRequest::findOrFail($id);
        $request->delete();

        return redirect()->route('admin.requests.index')
            ->with('success', 'Request deleted successfully');
    }
}
