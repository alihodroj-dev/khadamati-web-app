<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Payment;
use App\Models\User;
use App\Models\Service;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index', [
            // Requests
            'totalRequests' => ServiceRequest::count(),
            'pendingRequests' => ServiceRequest::where('status', 'pending')->count(),
            'completedRequests' => ServiceRequest::where('status', 'completed')->count(),

            // Users
            'totalUsers' => User::count(),
            'staffCount' => User::where('role', 'staff')->count(),

            // Services
            'totalServices' => Service::count(),

            // Payments
            'totalRevenue' => Payment::where('status', 'paid')->sum('amount'),
            'paidPayments' => Payment::where('status', 'paid')->count(),
            'pendingPayments' => Payment::where('status', 'pending')->count(),
        ]);
    }
}
