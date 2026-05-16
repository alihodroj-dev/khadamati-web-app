<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\ServiceRequest;
use App\Notifications\AppointmentUpdatedNotification;
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

            $appointments = Appointment::with([
                'user',
                'staff',
                'serviceRequest',
            ])->latest()->get();

        } elseif ($user->isStaff()) {

            $appointments = Appointment::with([
                'user',
                'staff',
                'serviceRequest',
            ])
                ->where('staff_id', $user->id)
                ->latest()
                ->get();

        } else {

            $appointments = Appointment::with([
                'user',
                'staff',
                'serviceRequest',
            ])
                ->where('user_id', $user->id)
                ->latest()
                ->get();
        }

        return $this->successResponse(
            AppointmentResource::collection($appointments),
            'Appointments retrieved successfully.'
        );
    }

    public function availability(Request $request)
    {
        $this->authorize('viewAny', Appointment::class);

        $validated = $request->validate([
            'date' => ['required', 'date', 'date_format:Y-m-d'],
            'staff_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $query = Appointment::query()
            ->whereDate('appointment_date', $validated['date'])
            ->whereNotIn('status', ['cancelled']);

        if (! empty($validated['staff_id'])) {
            $query->where('staff_id', $validated['staff_id']);
        }

        $unavailableTimes = $query
            ->orderBy('appointment_time')
            ->pluck('appointment_time')
            ->map(fn ($time) => Carbon::parse($time)->format('H:i'))
            ->unique()
            ->values()
            ->all();

        return $this->successResponse(
            [
                'date' => $validated['date'],
                'unavailable_times' => $unavailableTimes,
            ],
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

        $serviceRequest = ServiceRequest::findOrFail(
            $validated['service_request_id']
        );

        if ($serviceRequest->user_id !== auth()->id()) {

            return $this->errorResponse(
                'Unauthorized service request.',
                null,
                403
            );
        }

        $staffId = $validated['staff_id'] ?? $serviceRequest->assigned_staff_id;

        if ($staffId) {
            $slotTaken = Appointment::query()
                ->whereDate('appointment_date', $validated['appointment_date'])
                ->whereTime('appointment_time', $validated['appointment_time'])
                ->where('staff_id', $staffId)
                ->whereNotIn('status', ['cancelled'])
                ->exists();

            if ($slotTaken) {
                return $this->errorResponse(
                    'The selected appointment slot is unavailable.',
                    [
                        'appointment_time' => ['This time slot is already booked.'],
                    ],
                    422
                );
            }
        } else {
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

        $appointment->load([
            'user',
            'staff',
            'serviceRequest',
        ]);

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

        $appointment->load([
            'user',
            'staff',
            'serviceRequest',
        ]);

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

        $appointment->update($validated);

        $appointment->load([
            'user',
            'staff',
            'serviceRequest',
        ]);

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
}
