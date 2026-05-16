<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Payment;
use App\Models\User;
use App\Models\Service;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [

            // Requests
            'totalRequests'     => ServiceRequest::count(),
            'pendingRequests'   => ServiceRequest::where('status', 'pending')->count(),
            'completedRequests' => ServiceRequest::where('status', 'completed')->count(),
            'inProgressRequests'=> ServiceRequest::where('status', 'in_progress')->count(),

            // Users
            'totalUsers'  => User::count(),
            'staffCount'  => User::where('role', 'staff')->count(),
            'citizenCount'=> User::where('role', 'citizen')->count(),

            // Services
            'totalServices' => Service::count(),

            // Payments
            'totalRevenue'   => Payment::where('status', 'paid')->sum('amount'),
            'pendingPayments'=> Payment::where('status', 'pending')->count(),
            'paidPayments'   => Payment::where('status', 'paid')->count(),

            // Appointments
            'todayAppointments' => Appointment::whereDate('appointment_date', now()->toDateString())->count(),

            // Recent requests
            'recentRequests' => ServiceRequest::with(['user', 'service'])
                                ->latest()
                                ->take(5)
                                ->get(),
        ]);
    }
}
