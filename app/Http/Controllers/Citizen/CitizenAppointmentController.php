<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Appointment;
use App\Models\OfficeTimeSlot;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Notifications\AppointmentUpdatedNotification;

class CitizenAppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::where('user_id', auth()->id())
            ->with(['serviceRequest.service', 'staff'])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->paginate(10);

        return view('citizen.appointments.index', compact('appointments'));
    }

    public function create($requestId)
    {
        $serviceRequest = ServiceRequest::where('user_id', auth()->id())
            ->with('service')
            ->findOrFail($requestId);

        if (!$serviceRequest->service->requires_appointment) {
            return redirect()->route('citizen.requests.show', $serviceRequest->id)
                ->with('error', 'This service does not require an appointment.');
        }

        return view('citizen.appointments.create', compact('serviceRequest'));
    }

    public function getAvailableStaff(Request $request)
    {
        $date = Carbon::parse($request->date);
        
        $staff = User::where('role', 'staff')
            ->where('is_active', true)
            ->get();
        
        $result = [];
        foreach ($staff as $staffMember) {
            $result[] = [
                'id' => $staffMember->id,
                'name' => $staffMember->name,
                'office_name' => $staffMember->office ? $staffMember->office->name : 'Main Office',
            ];
        }
        
        return response()->json($result);
    }

    public function getAvailableSlots(Request $request, $staffId)
    {
        $date = Carbon::parse($request->date);
        $dayOfWeek = $date->dayOfWeek;
        
        $timeSlots = OfficeTimeSlot::where('staff_id', $staffId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->get();
        
        $availableSlots = [];
        
        foreach ($timeSlots as $slot) {
            $start = Carbon::parse($slot->start_time);
            $end = Carbon::parse($slot->end_time);
            $duration = $slot->slot_duration_minutes;
            
            while ($start < $end) {
                $slotTime = $start->format('H:i');
                
                $isBooked = Appointment::where('staff_id', $staffId)
                    ->whereDate('appointment_date', $date)
                    ->whereTime('appointment_time', $slotTime)
                    ->where('status', '!=', 'cancelled')
                    ->exists();
                
                if (!$isBooked) {
                    $availableSlots[] = [
                        'time' => $slotTime,
                        'display' => $start->format('h:i A'),
                    ];
                }
                
                $start->addMinutes($duration);
            }
        }
        
        return response()->json($availableSlots);
    }

    public function store(Request $request, $requestId)
    {
        $serviceRequest = ServiceRequest::where('user_id', auth()->id())->findOrFail($requestId);

        $request->validate([
            'staff_id' => ['required', 'exists:users,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string'],
        ]);

        $existing = Appointment::where('staff_id', $request->staff_id)
            ->whereDate('appointment_date', $request->appointment_date)
            ->whereTime('appointment_time', $request->appointment_time)
            ->exists();

        if ($existing) {
            return back()->withErrors(['appointment_time' => 'This time slot is no longer available.']);
        }

        $appointment = Appointment::create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => auth()->id(),
            'staff_id' => $request->staff_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'status' => 'scheduled',
            'notes' => $request->notes,
        ]);

        //  NOTIFY CITIZEN
        $citizen = auth()->user();
        $citizen->notify(new AppointmentUpdatedNotification(
            $appointment,
            'Appointment Confirmed',
            'Your appointment for ' . $serviceRequest->service->name . ' is scheduled on ' . Carbon::parse($request->appointment_date)->format('M d, Y') . ' at ' . $request->appointment_time
        ));

        // NOTIFY STAFF
        $staff = User::find($request->staff_id);
        if ($staff) {
            $staff->notify(new AppointmentUpdatedNotification(
                $appointment,
                'New Appointment',
                'You have a new appointment on ' . Carbon::parse($request->appointment_date)->format('M d, Y') . ' at ' . $request->appointment_time
            ));
        }

        return redirect()->route('citizen.appointments.show', $appointment->id)
            ->with('success', 'Appointment booked successfully!');
    }

    public function show($id)
    {
        $appointment = Appointment::where('user_id', auth()->id())
            ->with(['serviceRequest.service', 'staff'])
            ->findOrFail($id);

        return view('citizen.appointments.show', compact('appointment'));
    }

    public function cancel($id)
    {
        $appointment = Appointment::where('user_id', auth()->id())
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->findOrFail($id);

        $appointment->update(['status' => 'cancelled']);

        return redirect()->route('citizen.appointments.index')
            ->with('success', 'Appointment cancelled successfully.');
    }
}