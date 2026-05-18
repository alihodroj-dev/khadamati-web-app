<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Payment;
use App\Models\Appointment;
use Illuminate\Http\Request;

class CitizenDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        return view('citizen.dashboard', [
            'totalRequests' => ServiceRequest::where('user_id', $user->id)->count(),
            'pendingRequests' => ServiceRequest::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'under_review', 'requires_action'])
                ->count(),
            'completedRequests' => ServiceRequest::where('user_id', $user->id)
                ->where('status', 'completed')
                ->count(),
            'recentRequests' => ServiceRequest::where('user_id', $user->id)
                ->with(['service', 'office'])
                ->latest()
                ->take(5)
                ->get(),
            'upcomingAppointments' => Appointment::where('user_id', $user->id)
                ->whereIn('status', ['scheduled', 'confirmed'])
                ->whereDate('appointment_date', '>=', now())
                ->with(['serviceRequest.service', 'staff'])
                ->orderBy('appointment_date')
                ->orderBy('appointment_time')
                ->take(5)
                ->get(),
            'recentPayments' => Payment::where('user_id', $user->id)
                ->with('serviceRequest')
                ->latest()
                ->take(5)
                ->get(),
            'pendingPayments' => Payment::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count(),
        ]);
    }
}