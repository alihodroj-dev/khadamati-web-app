<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ServiceRequest;
use App\Support\StaffOfficeScope;

class StaffDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $requestQuery = StaffOfficeScope::applyServiceRequestScope(ServiceRequest::query(), $user);
        $appointmentQuery = StaffOfficeScope::applyAppointmentScope(Appointment::query(), $user);

        return view('staff.dashboard', [
            'assignedRequests' => (clone $requestQuery)->count(),
            'appointments' => (clone $appointmentQuery)->count(),
            'completedTasks' => (clone $requestQuery)->where('status', 'completed')->count(),
        ]);
    }
}
