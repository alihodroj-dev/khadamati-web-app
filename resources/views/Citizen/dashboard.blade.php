@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-semibold text-gray-800 mb-6">Citizen Dashboard</h1>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <x-card class="text-center">
            <i class="ti ti-clipboard-list text-3xl text-blue-600 mb-2"></i>
            <p class="text-2xl font-bold text-gray-800">{{ $totalRequests }}</p>
            <p class="text-sm text-gray-500">Total Requests</p>
        </x-card>
        
        <x-card class="text-center">
            <i class="ti ti-hourglass text-3xl text-yellow-600 mb-2"></i>
            <p class="text-2xl font-bold text-gray-800">{{ $pendingRequests }}</p>
            <p class="text-sm text-gray-500">Pending Requests</p>
        </x-card>
        
        <x-card class="text-center">
            <i class="ti ti-check-circle text-3xl text-green-600 mb-2"></i>
            <p class="text-2xl font-bold text-gray-800">{{ $completedRequests }}</p>
            <p class="text-sm text-gray-500">Completed</p>
        </x-card>
        
        <x-card class="text-center">
            <i class="ti ti-credit-card text-3xl text-red-600 mb-2"></i>
            <p class="text-2xl font-bold text-gray-800">{{ $pendingPayments }}</p>
            <p class="text-sm text-gray-500">Pending Payments</p>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Requests -->
        <x-card>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-800">Recent Requests</h2>
                <a href="{{ route('citizen.requests.index') }}" class="text-sm text-blue-600 hover:underline">View all</a>
            </div>
            
            @if($recentRequests->count() > 0)
                <div class="space-y-3">
                    @foreach($recentRequests as $request)
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-800">{{ $request->service->name }}</p>
                                <p class="text-xs text-gray-500">{{ $request->reference_number }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($request->status == 'pending') bg-yellow-100 text-yellow-800
                                @elseif($request->status == 'completed') bg-green-100 text-green-800
                                @elseif($request->status == 'rejected') bg-red-100 text-red-800
                                @else bg-blue-100 text-blue-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No requests yet</p>
            @endif
        </x-card>

        <!-- Upcoming Appointments -->
        <x-card>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-800">Upcoming Appointments</h2>
                <a href="{{ route('citizen.appointments.index') }}" class="text-sm text-blue-600 hover:underline">View all</a>
            </div>
            
            @if($upcomingAppointments->count() > 0)
                <div class="space-y-3">
                    @foreach($upcomingAppointments as $appointment)
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-800">{{ $appointment->serviceRequest->service->name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}
                                    at {{ $appointment->appointment_time }}
                                </p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                {{ ucfirst($appointment->status) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No upcoming appointments</p>
            @endif
        </x-card>
    </div>
</div>
@endsection