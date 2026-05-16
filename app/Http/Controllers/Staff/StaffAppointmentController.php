<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;

class StaffAppointmentController extends Controller
{
    /**
     * All assigned appointments
     */
    public function index()
    {
        $appointments = Appointment::with(['serviceRequest', 'user'])
            ->where('staff_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('staff.appointments.index', compact('appointments'));
    }

    /**
     * Today's schedule
     */
    public function today()
    {
        $appointments = Appointment::with(['serviceRequest', 'user'])
            ->where('staff_id', auth()->id())
            ->whereDate('appointment_date', now()->toDateString())
            ->orderBy('appointment_time')
            ->get();

        return view('staff.appointments.today', compact('appointments'));
    }

    /**
     * Show appointment
     */
    public function show($id)
    {
        $appointment = Appointment::with(['serviceRequest', 'user'])
            ->where('staff_id', auth()->id())
            ->findOrFail($id);

        return view('staff.appointments.show', compact('appointment'));
    }

    /**
     * Update status + notes
     */
    public function update(Request $request, $id)
    {
        $appointment = Appointment::where('staff_id', auth()->id())
            ->findOrFail($id);

        $appointment->update([
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Appointment updated successfully');
    }
}
