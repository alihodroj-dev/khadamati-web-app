<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\ServiceRequest;
use App\Traits\ApiResponse;
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
                'serviceRequest'
            ])->latest()->get();

        } elseif ($user->isStaff()) {

            $appointments = Appointment::with([
                'user',
                'staff',
                'serviceRequest'
            ])
            ->where('staff_id', $user->id)
            ->latest()
            ->get();

        } else {

            $appointments = Appointment::with([
                'user',
                'staff',
                'serviceRequest'
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

    public function store(Request $request)
    {
        $this->authorize('create', Appointment::class);

        $validated = $request->validate([
            'service_request_id' => [
                'required',
                'exists:service_requests,id'
            ],
            'appointment_date' => [
                'required',
                'date',
                'after_or_equal:today'
            ],
            'appointment_time' => [
                'required',
                'date_format:H:i'
            ],
            'notes' => [
                'nullable',
                'string'
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

        $appointment = Appointment::create([
            'service_request_id' => $validated['service_request_id'],
            'user_id' => auth()->id(),
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'scheduled',
        ]);

        $appointment->load([
            'user',
            'staff',
            'serviceRequest'
        ]);

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
            'serviceRequest'
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
                'after_or_equal:today'
            ],
            'appointment_time' => [
                'sometimes',
                'date_format:H:i'
            ],
            'status' => [
                'sometimes',
                'in:scheduled,confirmed,cancelled,completed,missed'
            ],
            'staff_id' => [
                'nullable',
                'exists:users,id'
            ],
            'notes' => [
                'nullable',
                'string'
            ],
        ]);

        $appointment->update($validated);

        $appointment->load([
            'user',
            'staff',
            'serviceRequest'
        ]);

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