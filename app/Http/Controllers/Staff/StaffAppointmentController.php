<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Support\StaffOfficeScope;

class StaffAppointmentController extends Controller
{
    /**
     * All assigned appointments
     */
    public function index()
    {
        $appointments = StaffOfficeScope::applyAppointmentScope(
            Appointment::with(['serviceRequest', 'user'])->latest(),
            auth()->user()
        )->paginate(10);

        return view('staff.appointments.index', compact('appointments'));
    }

    /**
     * Today's schedule
     */
    public function today()
    {
        $appointments = StaffOfficeScope::applyAppointmentScope(
            Appointment::with(['serviceRequest', 'user'])
                ->whereDate('appointment_date', now()->toDateString())
                ->orderBy('appointment_time'),
            auth()->user()
        )->get();

        return view('staff.appointments.today', compact('appointments'));
    }

    /**
     * Show appointment
     */
    public function show($id)
    {
        $appointment = StaffOfficeScope::applyAppointmentScope(
            Appointment::with(['serviceRequest', 'user']),
            auth()->user()
        )->findOrFail($id);

        return view('staff.appointments.show', compact('appointment'));
    }

    /**
     * Update status + notes
     */
    public function update(Request $request, $id)
    {
        $appointment = StaffOfficeScope::applyAppointmentScope(
            Appointment::query(),
            auth()->user()
        )->findOrFail($id);

        $appointment->update([
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Appointment updated successfully');
    }
}
