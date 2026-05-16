<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Http\Resources\ServiceRequestResource;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\RequestUpdatedNotification;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class StaffRequestController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $this->authorize('viewAny', ServiceRequest::class);

        $user = $request->user();
        $query = ServiceRequest::query()
            ->with(['user', 'service.category', 'assignedStaff', 'documents'])
            ->latest();

        if ($user->isStaff()) {
            $query->where('assigned_staff_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->get();

        return $this->successResponse(
            ServiceRequestResource::collection($requests),
            'Service requests retrieved successfully.'
        );
    }

    public function show(ServiceRequest $serviceRequest)
    {
        $this->authorize('view', $serviceRequest);

        $serviceRequest->load([
            'user',
            'service.category',
            'assignedStaff',
            'documents',
            'appointment',
            'payment',
        ]);

        return $this->successResponse(
            new ServiceRequestResource($serviceRequest),
            'Service request retrieved successfully.'
        );
    }

    public function updateStatus(Request $request, ServiceRequest $serviceRequest)
    {
        $this->authorize('updateStatus', $serviceRequest);

        $validated = $request->validate([
            'status' => [
                'required',
                'in:under_review,requires_action,approved,rejected,completed',
            ],
            'staff_notes' => ['nullable', 'string'],
            'rejection_reason' => ['required_if:status,rejected', 'nullable', 'string'],
        ]);

        $updateData = ['status' => $validated['status']];

        if (isset($validated['staff_notes'])) {
            $updateData['staff_notes'] = $validated['staff_notes'];
        }

        if ($validated['status'] === 'rejected') {
            $updateData['rejection_reason'] = $validated['rejection_reason'];
        }

        if (in_array($validated['status'], ['approved', 'under_review'])) {
            $updateData['reviewed_at'] = now();
        }

        if ($validated['status'] === 'completed') {
            $updateData['reviewed_at'] = now();
            $updateData['completed_at'] = now();
        }

        $serviceRequest->update($updateData);
        $serviceRequest->load(['user', 'service', 'assignedStaff', 'documents']);

        $serviceRequest->user?->notify(new RequestUpdatedNotification(
            $serviceRequest,
            'Request status updated',
            'Your request status is now '.$serviceRequest->status.'.'
        ));

        return $this->successResponse(
            new ServiceRequestResource($serviceRequest),
            'Service request status updated.'
        );
    }

    public function assign(Request $request, ServiceRequest $serviceRequest)
    {
        $this->authorize('assign', $serviceRequest);

        $validated = $request->validate([
            'staff_id' => ['required', 'exists:users,id'],
        ]);

        $staffUser = User::findOrFail($validated['staff_id']);

        if (! $staffUser->isStaff()) {
            return $this->errorResponse('The selected user is not a staff member.', null, 422);
        }

        $serviceRequest->update([
            'assigned_staff_id' => $validated['staff_id'],
            'status' => $serviceRequest->status === 'pending' ? 'under_review' : $serviceRequest->status,
            'reviewed_at' => now(),
        ]);

        $serviceRequest->load(['user', 'service', 'assignedStaff']);

        $staffUser->notify(new RequestUpdatedNotification(
            $serviceRequest,
            'Request assigned',
            'A service request has been assigned to you.'
        ));

        $serviceRequest->user?->notify(new RequestUpdatedNotification(
            $serviceRequest,
            'Request under review',
            'Your request has been assigned to staff for review.'
        ));

        return $this->successResponse(
            new ServiceRequestResource($serviceRequest),
            'Service request assigned successfully.'
        );
    }

    public function appointments(Request $request)
    {
        $user = $request->user();
        $query = Appointment::query()
            ->with(['user', 'staff', 'serviceRequest.service'])
            ->latest();

        if ($user->isStaff()) {
            $query->where('staff_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $this->successResponse(
            AppointmentResource::collection($query->get()),
            'Appointments retrieved successfully.'
        );
    }

    public function updateAppointment(Request $request, Appointment $appointment)
    {
        $this->authorize('update', $appointment);

        $validated = $request->validate([
            'status' => ['sometimes', 'in:scheduled,confirmed,cancelled,completed,missed'],
            'staff_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ]);

        if (isset($validated['staff_id'])) {
            if (! $request->user()->isAdmin()) {
                return $this->errorResponse('Only admins can assign appointment staff.', null, 403);
            }

            $staffUser = User::findOrFail($validated['staff_id']);

            if (! $staffUser->isStaff()) {
                return $this->errorResponse('The selected user is not a staff member.', null, 422);
            }
        }

        $appointment->update($validated);
        $appointment->load(['user', 'staff', 'serviceRequest']);

        return $this->successResponse(
            new AppointmentResource($appointment),
            'Appointment updated successfully.'
        );
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $requestQuery = ServiceRequest::query();
        $appointmentQuery = Appointment::query();
        $paymentQuery = Payment::query();

        if ($user->isStaff()) {
            $requestQuery->where('assigned_staff_id', $user->id);
            $appointmentQuery->where('staff_id', $user->id);
            $paymentQuery->whereHas('serviceRequest', function ($q) use ($user) {
                $q->where('assigned_staff_id', $user->id);
            });
        }

        return $this->successResponse(
            [
                'requests' => [
                    'total' => (clone $requestQuery)->count(),
                    'pending' => (clone $requestQuery)->where('status', 'pending')->count(),
                    'under_review' => (clone $requestQuery)->where('status', 'under_review')->count(),
                    'requires_action' => (clone $requestQuery)->where('status', 'requires_action')->count(),
                    'completed' => (clone $requestQuery)->where('status', 'completed')->count(),
                ],
                'appointments' => [
                    'total' => (clone $appointmentQuery)->count(),
                    'scheduled' => (clone $appointmentQuery)->where('status', 'scheduled')->count(),
                    'confirmed' => (clone $appointmentQuery)->where('status', 'confirmed')->count(),
                    'completed' => (clone $appointmentQuery)->where('status', 'completed')->count(),
                ],
                'payments' => [
                    'pending' => (clone $paymentQuery)->where('status', 'pending')->count(),
                    'paid' => (clone $paymentQuery)->where('status', 'paid')->count(),
                ],
            ],
            'Staff dashboard retrieved successfully.'
        );
    }
}
