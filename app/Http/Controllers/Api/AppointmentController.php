<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Office;
use App\Models\ServiceRequest;
use App\Notifications\AppointmentUpdatedNotification;
use App\Support\AppointmentAvailabilityBuilder;
use App\Support\StaffOfficeScope;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {

            $appointments = Appointment::with($this->appointmentRelations())
                ->latest()->get();

        } elseif ($user->isStaff()) {

            $appointments = StaffOfficeScope::applyAppointmentScope(
                Appointment::with($this->appointmentRelations())->latest(),
                $user
            )->get();

        } else {

            $appointments = Appointment::with($this->appointmentRelations())
                ->where('user_id', $user->id)
                ->latest()
                ->get();
        }

        return $this->successResponse(
            AppointmentResource::collection($appointments),
            'Appointments retrieved successfully.'
        );
    }

    public function availability(Request $request, AppointmentAvailabilityBuilder $availabilityBuilder)
    {
        $this->authorize('viewAny', Appointment::class);

        $validated = $request->validate([
            'date' => ['required', 'date', 'date_format:Y-m-d'],
            'service_request_id' => ['required', 'integer', 'exists:service_requests,id'],
        ]);

        $serviceRequest = ServiceRequest::query()
            ->with(['office', 'service'])
            ->findOrFail($validated['service_request_id']);

        if ($serviceRequest->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            return $this->errorResponse(
                'You are not allowed to view availability for this service request.',
                null,
                403
            );
        }

        if (! $serviceRequest->service?->requires_appointment) {
            return $this->errorResponse(
                'This service request does not require an appointment.',
                [
                    'service_request_id' => ['Appointments are not available for this service.'],
                ],
                422
            );
        }

        $office = $serviceRequest->office;
        $office = $office instanceof Office && $office->is_active ? $office : null;
        $staffId = $serviceRequest->assigned_staff_id
            ? (int) $serviceRequest->assigned_staff_id
            : null;

        $bookedTimes = $this->bookedTimesForDate(
            $validated['date'],
            $staffId,
            $office?->id,
            null,
            $availabilityBuilder
        );

        $payload = $availabilityBuilder->build(
            $validated['date'],
            $office,
            $bookedTimes,
            $staffId
        );

        return $this->successResponse(
            $payload,
            'Appointment availability retrieved successfully.'
        );
    }

    public function store(Request $request)
    {
        $this->authorize('create', Appointment::class);

        $validated = $request->validate([
            'service_request_id' => [
                'required',
                'exists:service_requests,id',
            ],
            'appointment_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'appointment_time' => [
                'required',
                'date_format:H:i',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
            'staff_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
        ]);

        $serviceRequest = ServiceRequest::query()
            ->with(['office', 'service'])
            ->findOrFail($validated['service_request_id']);

        if ($serviceRequest->user_id !== auth()->id()) {

            return $this->errorResponse(
                'Unauthorized service request.',
                null,
                403
            );
        }

        if (! $serviceRequest->service?->requires_appointment) {
            return $this->errorResponse(
                'This service request does not require an appointment.',
                [
                    'service_request_id' => ['You cannot book an appointment for this service.'],
                ],
                422
            );
        }

        $availabilityBuilder = app(AppointmentAvailabilityBuilder::class);
        $staffId = $validated['staff_id'] ?? $serviceRequest->assigned_staff_id;
        $office = $serviceRequest->office;

        $bookedTimes = $this->bookedTimesForDate(
            $validated['appointment_date'],
            $staffId ? (int) $staffId : null,
            $office?->id,
            null,
            $availabilityBuilder
        );

        if (! $availabilityBuilder->isSlotAvailable(
            $validated['appointment_date'],
            $validated['appointment_time'],
            $office,
            $staffId ? (int) $staffId : null,
            $bookedTimes
        )) {
            return $this->errorResponse(
                'The selected appointment slot is unavailable.',
                [
                    'appointment_time' => ['This time is outside available office slots or is already booked.'],
                ],
                422
            );
        }

        if (! $staffId) {
            $existingForRequest = Appointment::query()
                ->where('service_request_id', $serviceRequest->id)
                ->where('user_id', auth()->id())
                ->whereNotIn('status', ['cancelled'])
                ->exists();

            if ($existingForRequest) {
                return $this->errorResponse(
                    'An appointment already exists for this service request.',
                    [
                        'service_request_id' => ['You already have an appointment for this request.'],
                    ],
                    422
                );
            }
        }

        $appointment = Appointment::create([
            'service_request_id' => $validated['service_request_id'],
            'user_id' => auth()->id(),
            'staff_id' => $staffId,
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'scheduled',
        ]);

        $appointment->load($this->appointmentRelations());

        $appointment->user?->notify(new AppointmentUpdatedNotification(
            $appointment,
            'Appointment booked',
            'Your appointment has been booked successfully.'
        ));

        return $this->successResponse(
            new AppointmentResource($appointment),
            'Appointment booked successfully.',
            201
        );
    }

    public function show(Appointment $appointment)
    {
        $this->authorize('view', $appointment);

        $appointment->load($this->appointmentRelations());

        return $this->successResponse(
            new AppointmentResource($appointment),
            'Appointment retrieved successfully.'
        );
    }

    public function update(Request $request, Appointment $appointment)
    {
        $this->authorize('update', $appointment);

        $validated = $request->validate([
            'appointment_date' => [
                'sometimes',
                'date',
                'after_or_equal:today',
            ],
            'appointment_time' => [
                'sometimes',
                'date_format:H:i',
            ],
            'status' => [
                'sometimes',
                'in:scheduled,confirmed,cancelled,completed,missed',
            ],
            'staff_id' => [
                'nullable',
                'exists:users,id',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        if (isset($validated['appointment_date']) || isset($validated['appointment_time'])) {
            $appointment->loadMissing('serviceRequest.office');

            $date = $validated['appointment_date']
                ?? $appointment->appointment_date->format('Y-m-d');
            $time = $validated['appointment_time']
                ?? Carbon::parse($appointment->appointment_time)->format('H:i');
            $staffId = $validated['staff_id'] ?? $appointment->staff_id;
            $office = $appointment->serviceRequest?->office;

            $availabilityBuilder = app(AppointmentAvailabilityBuilder::class);

            $bookedTimes = $this->bookedTimesForDate(
                $date,
                $staffId ? (int) $staffId : null,
                $office?->id,
                $appointment->id,
                $availabilityBuilder
            );

            if (($validated['status'] ?? $appointment->status) !== 'cancelled'
                && ! $availabilityBuilder->isSlotAvailable(
                    $date,
                    $time,
                    $office,
                    $staffId ? (int) $staffId : null,
                    $bookedTimes
                )) {
                return $this->errorResponse(
                    'The selected appointment slot is unavailable.',
                    [
                        'appointment_time' => ['This time is outside available office slots or is already booked.'],
                    ],
                    422
                );
            }
        }

        $appointment->update($validated);

        $appointment->load($this->appointmentRelations());

        $appointment->user?->notify(new AppointmentUpdatedNotification(
            $appointment,
            'Appointment updated',
            'Your appointment has been updated.'
        ));

        $appointment->staff?->notify(new AppointmentUpdatedNotification(
            $appointment,
            'Appointment updated',
            'An assigned appointment has been updated.'
        ));

        return $this->successResponse(
            new AppointmentResource($appointment),
            'Appointment updated successfully.'
        );
    }

    public function destroy(Appointment $appointment)
    {
        $this->authorize('delete', $appointment);

        $appointment->delete();

        return $this->successResponse(
            null,
            'Appointment deleted successfully.'
        );
    }

    /**
     * @return list<string>
     */
    private function bookedTimesForDate(
        string $date,
        ?int $staffId,
        ?int $officeId,
        ?int $excludeAppointmentId = null,
        ?AppointmentAvailabilityBuilder $availabilityBuilder = null,
    ): array {
        $query = Appointment::query()
            ->whereDate('appointment_date', $date)
            ->whereNotIn('status', ['cancelled']);

        if ($staffId !== null) {
            $query->where('staff_id', $staffId);
        } elseif ($officeId !== null) {
            $query->whereHas('serviceRequest', function ($serviceRequestQuery) use ($officeId) {
                $serviceRequestQuery->where('office_id', $officeId);
            });
        }

        if ($excludeAppointmentId !== null) {
            $query->where('id', '!=', $excludeAppointmentId);
        }

        $startTimes = $query
            ->orderBy('appointment_time')
            ->pluck('appointment_time')
            ->map(fn ($time) => Carbon::parse($time)->format('H:i'))
            ->unique()
            ->values()
            ->all();

        $builder = $availabilityBuilder ?? app(AppointmentAvailabilityBuilder::class);

        return $builder->occupiedSlotsForAppointments($date, $startTimes);
    }

    /**
     * @return list<string>
     */
    private function appointmentRelations(): array
    {
        return [
            'user',
            'staff',
            'serviceRequest.office',
            'serviceRequest.service',
        ];
    }
}
