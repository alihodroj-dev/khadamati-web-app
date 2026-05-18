<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Payment;
use App\Models\Appointment;
use Illuminate\Notifications\DatabaseNotification; // CHANGE THIS LINE
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CitizenDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get all service requests for the citizen
        $requests = ServiceRequest::where('user_id', $user->id)->get();
        
        // Statistics
        $stats = [
            'total_requests' => $requests->count(),
            'pending_requests' => $requests->whereIn('status', ['pending', 'under_review', 'requires_action'])->count(),
            'approved_requests' => $requests->where('status', 'approved')->count(),
            'completed_requests' => $requests->where('status', 'completed')->count(),
            'rejected_requests' => $requests->where('status', 'rejected')->count(),
            'cancelled_requests' => $requests->where('status', 'cancelled')->count(),
        ];
        
        // Payment statistics
        $payments = Payment::where('user_id', $user->id)->get();
        $stats['total_paid'] = $payments->where('status', 'paid')->sum('amount');
        $stats['pending_payments'] = $payments->where('status', 'pending')->count();
        
        // Recent requests (last 5)
        $recentRequests = ServiceRequest::where('user_id', $user->id)
            ->with(['service', 'office'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        // Upcoming appointments
        $upcomingAppointments = Appointment::where('user_id', $user->id)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->whereDate('appointment_date', '>=', now())
            ->with(['serviceRequest.service', 'staff'])
            ->orderBy('appointment_date', 'asc')
            ->orderBy('appointment_time', 'asc')
            ->take(5)
            ->get();
        
        // Recent payments
        $recentPayments = Payment::where('user_id', $user->id)
            ->with('serviceRequest')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        // Unread notifications count - FIXED
        $unreadNotificationsCount = DatabaseNotification::where('notifiable_id', $user->id)
            ->where('notifiable_type', get_class($user))
            ->whereNull('read_at')
            ->count();
        
        // Recent notifications - FIXED
        $recentNotifications = DatabaseNotification::where('notifiable_id', $user->id)
            ->where('notifiable_type', get_class($user))
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        // Monthly request chart data (last 6 months)
        $monthlyData = $this->getMonthlyRequestData($user->id);
        
        // Status distribution for pie chart
        $statusDistribution = [
            'pending' => $stats['pending_requests'],
            'approved' => $stats['approved_requests'],
            'completed' => $stats['completed_requests'],
            'rejected' => $stats['rejected_requests'],
            'cancelled' => $stats['cancelled_requests'],
        ];
        
        // Quick actions menu
        $quickActions = [
            ['name' => 'Browse Services', 'route' => 'citizen.services.index', 'icon' => 'ti-search', 'color' => 'blue'],
            ['name' => 'View My Requests', 'route' => 'citizen.requests.index', 'icon' => 'ti-clipboard-list', 'color' => 'green'],
            ['name' => 'Make a Payment', 'route' => 'citizen.payments.index', 'icon' => 'ti-credit-card', 'color' => 'purple'],
            ['name' => 'Book Appointment', 'route' => 'citizen.appointments.index', 'icon' => 'ti-calendar', 'color' => 'orange'],
            ['name' => 'Update Profile', 'route' => 'citizen.profile.edit', 'icon' => 'ti-user', 'color' => 'gray'],
        ];
        
        return view('citizen.dashboard', compact(
            'stats',
            'recentRequests',
            'upcomingAppointments',
            'recentPayments',
            'unreadNotificationsCount',
            'recentNotifications',
            'monthlyData',
            'statusDistribution',
            'quickActions'
        ));
    }
    
    private function getMonthlyRequestData($userId)
    {
        $months = [];
        $counts = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $months[] = $month->format('M Y');
            
            $count = ServiceRequest::where('user_id', $userId)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            
            $counts[] = $count;
        }
        
        return ['months' => $months, 'counts' => $counts];
    }
}