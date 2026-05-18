<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Http\Resources\ServiceRequestResource;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\AppointmentUpdatedNotification;
use App\Notifications\RequestUpdatedNotification;
use App\Support\ServiceRequestAssignment;
use App\Support\ServiceRequestStatus;
use App\Support\ServiceRequestStatusUpdater;
use App\Support\StaffOfficeScope;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StaffRequestController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $this->authorize('viewAny', ServiceRequest::class);

        $validated = $request->validate([
            'status' => ['nullable', ServiceRequestStatus::validationRule(ServiceRequestStatus::all())],
            'assignment' => ['nullable', 'in:assigned,unassigned'],
        ]);

        $user = $request->user();
        $query = ServiceRequest::query()
            ->with(['user', 'service.category', 'assignedStaff', 'office', 'documents'])
            ->latest();

        StaffOfficeScope::applyServiceRequestScope($query, $user);
        $this->applyListFilters($query, $validated);

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
            'office',
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
                ServiceRequestStatus::validationRule(ServiceRequestStatus::staffUpdatable()),
            ],
            'staff_notes' => ['nullable', 'string'],
            'rejection_reason' => ['required_if:status,'.ServiceRequestStatus::REJECTED, 'nullable', 'string'],
        ]);

        ServiceRequestStatusUpdater::apply(
            $serviceRequest,
            $validated['status'],
            $validated['staff_notes'] ?? null,
            $validated['rejection_reason'] ?? null
        );

        $serviceRequest->load(['user', 'service', 'assignedStaff', 'office', 'documents', 'payment']);

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

        try {
            ServiceRequestAssignment::assign($serviceRequest, $staffUser);
        } catch (ValidationException $exception) {
            return $this->errorResponse(
                collect($exception->errors())->flatten()->first() ?? 'Invalid assignment.',
                $exception->errors(),
                422
            );
        }

        $serviceRequest->load(['user', 'service', 'assignedStaff', 'office']);

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

        StaffOfficeScope::applyAppointmentScope($query, $user);

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

        $appointment->user?->notify(new AppointmentUpdatedNotification(
            $appointment,
            'Appointment updated',
            'Your appointment status has been updated to '.$appointment->status.'.'
        ));

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
            StaffOfficeScope::applyServiceRequestScope($requestQuery, $user);
            StaffOfficeScope::applyAppointmentScope($appointmentQuery, $user);
            StaffOfficeScope::applyPaymentScope($paymentQuery, $user);
        }

        return $this->successResponse(
            [
                'requests' => [
                    'total' => (clone $requestQuery)->count(),
                    'pending' => (clone $requestQuery)->where('status', ServiceRequestStatus::PENDING)->count(),
                    'under_review' => (clone $requestQuery)->where('status', ServiceRequestStatus::UNDER_REVIEW)->count(),
                    'requires_action' => (clone $requestQuery)->where('status', ServiceRequestStatus::REQUIRES_ACTION)->count(),
                    'completed' => (clone $requestQuery)->where('status', ServiceRequestStatus::COMPLETED)->count(),
                    'unassigned' => (clone $requestQuery)->whereNull('assigned_staff_id')->count(),
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

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyListFilters($query, array $filters): void
    {
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['assignment'])) {
            if ($filters['assignment'] === 'assigned') {
                $query->whereNotNull('assigned_staff_id');
            }

            if ($filters['assignment'] === 'unassigned') {
                $query->whereNull('assigned_staff_id');
            }
        }
    }
}
