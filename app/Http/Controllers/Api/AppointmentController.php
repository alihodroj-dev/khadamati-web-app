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
            'staff_id' => ['nullable', 'integer', 'exists:users,id'],
            'service_request_id' => ['nullable', 'integer', 'exists:service_requests,id'],
        ]);

        $office = $this->resolveOfficeForAvailability($request, $validated);

        $query = Appointment::query()
            ->whereDate('appointment_date', $validated['date'])
            ->whereNotIn('status', ['cancelled']);

        if (! empty($validated['staff_id'])) {
            $query->where('staff_id', $validated['staff_id']);
        }

        $bookedTimes = $query
            ->orderBy('appointment_time')
            ->pluck('appointment_time')
            ->map(fn ($time) => Carbon::parse($time)->format('H:i'))
            ->unique()
            ->values()
            ->all();

        $payload = $availabilityBuilder->build(
            $validated['date'],
            $office,
            $bookedTimes
        );

        return $this->successResponse(
            $payload,
            'Appointment availability retrieved successfully.'
        );
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function resolveOfficeForAvailability(Request $request, array $validated): ?Office
    {
        if (empty($validated['service_request_id'])) {
            return null;
        }

        $serviceRequest = ServiceRequest::query()
            ->with('office')
            ->findOrFail($validated['service_request_id']);

        if ($serviceRequest->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403, 'You are not allowed to view availability for this service request.');
        }

        $office = $serviceRequest->office;

        return $office instanceof Office && $office->is_active ? $office : null;
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
