@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
    <!-- Welcome Section -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Welcome back, {{ auth()->user()->first_name ?? auth()->user()->name }}!</h1>
        <p class="text-gray-500 mt-1">Here's what's happening with your government services today.</p>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5 mb-8">
        
        <!-- Total Requests -->
        <div class="bg-white rounded-xl p-5 shadow-sm hover:shadow-md transition" style="border: 0.5px solid #e5e7eb;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Requests</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_requests'] }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="ti ti-files text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <!-- Pending Requests -->
        <div class="bg-white rounded-xl p-5 shadow-sm hover:shadow-md transition" style="border: 0.5px solid #e5e7eb;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Pending Requests</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending_requests'] }}</p>
                </div>
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="ti ti-hourglass text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <!-- Completed Requests -->
        <div class="bg-white rounded-xl p-5 shadow-sm hover:shadow-md transition" style="border: 0.5px solid #e5e7eb;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Completed</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['completed_requests'] }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="ti ti-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <!-- Total Paid -->
        <div class="bg-white rounded-xl p-5 shadow-sm hover:shadow-md transition" style="border: 0.5px solid #e5e7eb;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Paid</p>
                    <p class="text-2xl font-bold text-purple-600">${{ number_format($stats['total_paid'], 2) }}</p>
                </div>
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="ti ti-wallet text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <!-- Pending Payments -->
        <div class="bg-white rounded-xl p-5 shadow-sm hover:shadow-md transition" style="border: 0.5px solid #e5e7eb;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Pending Payments</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['pending_payments'] }}</p>
                </div>
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="ti ti-credit-card text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
        
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        
        <!-- Monthly Requests Chart -->
        <div class="bg-white rounded-xl p-5 shadow-sm" style="border: 0.5px solid #e5e7eb;">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-gray-900">Monthly Requests</h3>
                <i class="ti ti-chart-bar text-gray-400"></i>
            </div>
            <canvas id="requestsChart" height="200"></canvas>
        </div>
        
        <!-- Status Distribution Chart -->
        <div class="bg-white rounded-xl p-5 shadow-sm" style="border: 0.5px solid #e5e7eb;">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-gray-900">Request Status</h3>
                <i class="ti ti-pie-chart text-gray-400"></i>
            </div>
            <canvas id="statusChart" height="200"></canvas>
        </div>
        
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column (2/3 width on desktop) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Quick Actions -->
            <div class="bg-white rounded-xl p-5 shadow-sm" style="border: 0.5px solid #e5e7eb;">
                <h3 class="font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                    @foreach($quickActions as $action)
                        <a href="{{ route($action['route']) }}" 
                           class="flex flex-col items-center p-3 rounded-lg hover:bg-gray-50 transition group">
                            <div class="w-12 h-12 rounded-lg bg-{{ $action['color'] }}-100 flex items-center justify-center mb-2 group-hover:scale-110 transition">
                                <i class="ti {{ $action['icon'] }} text-{{ $action['color'] }}-600 text-xl"></i>
                            </div>
                            <span class="text-xs text-gray-600 text-center">{{ $action['name'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            
            <!-- Recent Requests -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden" style="border: 0.5px solid #e5e7eb;">
                <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-900">Recent Requests</h3>
                    <a href="{{ route('citizen.requests.index') }}" class="text-sm text-blue-600 hover:underline">View all</a>
                </div>
                
                @if($recentRequests->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($recentRequests as $request)
                            <div class="px-5 py-4 hover:bg-gray-50 transition">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-sm font-medium text-gray-900">{{ $request->service->name ?? 'Unknown Service' }}</span>
                                            <span class="px-2 py-0.5 text-xs rounded-full
                                                @if($request->status == 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($request->status == 'under_review') bg-blue-100 text-blue-800
                                                @elseif($request->status == 'approved') bg-green-100 text-green-800
                                                @elseif($request->status == 'completed') bg-emerald-100 text-emerald-800
                                                @elseif($request->status == 'rejected') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500">Ref: {{ $request->reference_number }}</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ $request->created_at->diffForHumans() }}</p>
                                    </div>
                                    <a href="{{ route('citizen.requests.show', $request->id) }}" 
                                       class="text-blue-600 hover:text-blue-800 text-sm">
                                        View →
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-5 py-8 text-center">
                        <i class="ti ti-clipboard-list text-gray-300 text-4xl mb-2 block"></i>
                        <p class="text-gray-500">No requests yet</p>
                        <a href="{{ route('citizen.services.index') }}" class="text-sm text-blue-600 hover:underline mt-2 inline-block">
                            Browse Services →
                        </a>
                    </div>
                @endif
            </div>
            
            <!-- Upcoming Appointments -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden" style="border: 0.5px solid #e5e7eb;">
                <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-900">Upcoming Appointments</h3>
                    <a href="{{ route('citizen.appointments.index') }}" class="text-sm text-blue-600 hover:underline">View all</a>
                </div>
                
                @if($upcomingAppointments->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($upcomingAppointments as $appointment)
                            <div class="px-5 py-4 hover:bg-gray-50 transition">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $appointment->serviceRequest->service->name ?? 'Unknown' }}</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <i class="ti ti-calendar text-gray-400 mr-1"></i>
                                            {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}
                                            at {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}
                                        </p>
                                        <p class="text-xs text-gray-400">With: {{ $appointment->staff->name ?? 'Staff' }}</p>
                                    </div>
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                        {{ ucfirst($appointment->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-5 py-8 text-center">
                        <i class="ti ti-calendar text-gray-300 text-4xl mb-2 block"></i>
                        <p class="text-gray-500">No upcoming appointments</p>
                    </div>
                @endif
            </div>
            
        </div>
        
        <!-- Right Column (1/3 width on desktop) -->
        <div class="space-y-6">
            
            <!-- Profile Summary -->
            <div class="bg-gradient-to-r from-blue-900 to-blue-800 rounded-xl p-5 text-white shadow-sm">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                        <i class="ti ti-user text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="font-semibold">{{ auth()->user()->first_name ?? auth()->user()->name }}</p>
                        <p class="text-xs text-white/70">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <div class="border-t border-white/20 pt-3 mt-2">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-white/70">Member since</span>
                        <span>{{ auth()->user()->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-white/70">National ID</span>
                        <span>{{ auth()->user()->national_id ?? 'Not set' }}</span>
                    </div>
                </div>
                <a href="{{ route('citizen.profile.edit') }}" class="block text-center mt-3 text-sm text-white/80 hover:text-white transition">
                    Edit Profile →
                </a>
            </div>
            
            <!-- Notifications -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden" style="border: 0.5px solid #e5e7eb;">
                <div class="px-5 py-3 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="ti ti-bell"></i>
                        Notifications
                        @if($unreadNotificationsCount > 0)
                            <span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $unreadNotificationsCount }}</span>
                        @endif
                    </h3>
                    <a href="{{ route('notifications.index') }}" class="text-xs text-blue-600 hover:underline">View all</a>
                </div>
                
                @if($recentNotifications->count() > 0)
                    <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                        @foreach($recentNotifications as $notification)
                            <div class="px-4 py-3 hover:bg-gray-50 transition {{ is_null($notification->read_at) ? 'bg-blue-50/30' : '' }}">
                                <div class="flex gap-2">
                                    <i class="ti ti-info-circle text-blue-500 text-sm mt-0.5"></i>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs text-gray-800">{{ $notification->data['title'] ?? 'Notification' }}</p>
                                        <p class="text-xs text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-5 py-8 text-center">
                        <i class="ti ti-bell-off text-gray-300 text-3xl mb-2 block"></i>
                        <p class="text-sm text-gray-500">No notifications yet</p>
                    </div>
                @endif
            </div>
            
            <!-- Recent Payments -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden" style="border: 0.5px solid #e5e7eb;">
                <div class="px-5 py-3 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-900">Recent Payments</h3>
                    <a href="{{ route('citizen.payments.index') }}" class="text-xs text-blue-600 hover:underline">View all</a>
                </div>
                
                @if($recentPayments->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($recentPayments as $payment)
                            <div class="px-4 py-3">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">${{ number_format($payment->amount, 2) }}</p>
                                        <p class="text-xs text-gray-500">{{ $payment->created_at->format('M d, Y') }}</p>
                                    </div>
                                    <span class="px-2 py-1 text-xs rounded-full
                                        @if($payment->status == 'paid') bg-green-100 text-green-800
                                        @elseif($payment->status == 'pending') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-5 py-8 text-center">
                        <i class="ti ti-credit-card text-gray-300 text-3xl mb-2 block"></i>
                        <p class="text-sm text-gray-500">No payments yet</p>
                    </div>
                @endif
            </div>
            
        </div>
        
    </div>

</div>

<!-- Add Chart.js for charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Monthly Requests Chart
    const monthlyData = @json($monthlyData);
    const ctx1 = document.getElementById('requestsChart').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: monthlyData.months,
            datasets: [{
                label: 'Requests',
                data: monthlyData.counts,
                borderColor: '#1e3a5f',
                backgroundColor: 'rgba(30, 58, 95, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
    
    // Status Distribution Chart
    const statusData = @json($statusDistribution);
    const ctx2 = document.getElementById('statusChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Approved', 'Completed', 'Rejected', 'Cancelled'],
            datasets: [{
                data: [statusData.pending, statusData.approved, statusData.completed, statusData.rejected, statusData.cancelled],
                backgroundColor: ['#eab308', '#3b82f6', '#22c55e', '#ef4444', '#6b7280'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>

@endsection