<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Appointment;
use Illuminate\Http\Request;

class StaffDashboardController extends Controller
{
    public function index()
    {
        return view('staff.dashboard', [
            'assignedRequests' => ServiceRequest::where('assigned_staff_id', auth()->id())->count(),
            'appointments' => Appointment::where('staff_id', auth()->id())->count(),
            'completedTasks' => ServiceRequest::where('assigned_staff_id', auth()->id())
                                ->where('status', 'completed')->count(),
        ]);
    }
}
