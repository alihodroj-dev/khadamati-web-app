@extends('layouts.app')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold">Admin Dashboard</h1>
    <p class="text-sm text-gray-500">System overview</p>
</div>

{{-- ROW 1: Requests --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">

    <div class="bg-white p-6 rounded-xl shadow">
        <p class="text-sm text-gray-500">Total Requests</p>
        <h2 class="text-2xl font-bold mt-1">{{ $totalRequests }}</h2>
    </div>

    <div class="bg-white p-6 rounded-xl shadow">
        <p class="text-sm text-gray-500">Pending</p>
        <h2 class="text-2xl font-bold mt-1 text-yellow-600">{{ $pendingRequests }}</h2>
    </div>

    <div class="bg-white p-6 rounded-xl shadow">
        <p class="text-sm text-gray-500">In Progress</p>
        <h2 class="text-2xl font-bold mt-1 text-blue-600">{{ $inProgressRequests }}</h2>
    </div>

    <div class="bg-white p-6 rounded-xl shadow">
        <p class="text-sm text-gray-500">Completed</p>
        <h2 class="text-2xl font-bold mt-1 text-green-600">{{ $completedRequests }}</h2>
    </div>

</div>

{{-- ROW 2: Users, Services, Appointments --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">

    <div class="bg-white p-6 rounded-xl shadow">
        <p class="text-sm text-gray-500">Total Users</p>
        <h2 class="text-2xl font-bold mt-1">{{ $totalUsers }}</h2>
        <p class="text-xs text-gray-400 mt-1">Staff: {{ $staffCount }} · Citizens: {{ $citizenCount }}</p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow">
        <p class="text-sm text-gray-500">Total Services</p>
        <h2 class="text-2xl font-bold mt-1">{{ $totalServices }}</h2>
    </div>

    <div class="bg-white p-6 rounded-xl shadow">
        <p class="text-sm text-gray-500">Today's Appointments</p>
        <h2 class="text-2xl font-bold mt-1 text-indigo-600">{{ $todayAppointments }}</h2>
    </div>

    <div class="bg-white p-6 rounded-xl shadow">
        <p class="text-sm text-gray-500">Total Revenue</p>
        <h2 class="text-2xl font-bold mt-1 text-green-600">${{ number_format($totalRevenue, 2) }}</h2>
        <p class="text-xs text-gray-400 mt-1">Pending payments: {{ $pendingPayments }}</p>
    </div>

</div>


{{-- ROW 3: Recent Requests --}}
<div class="bg-white p-6 rounded-xl shadow">

    <div class="flex justify-between items-center mb-4">
        <h2 class="font-bold text-lg">Recent Requests</h2>
        <a href="{{ route('admin.requests.index') }}"
           class="text-blue-600 text-sm hover:underline">
            View All
        </a>
    </div>

    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-gray-500 border-b">
                <th class="pb-2">Reference</th>
                <th class="pb-2">Citizen</th>
                <th class="pb-2">Service</th>
                <th class="pb-2">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentRequests as $req)
                <tr class="border-t hover:bg-gray-50">
                    <td class="py-2 font-semibold">
                        <a href="{{ route('admin.requests.show', $req->id) }}"
                           class="text-blue-600 hover:underline">
                            {{ $req->reference_number }}
                        </a>
                    </td>
                    <td class="py-2">{{ $req->user->name ?? '-' }}</td>
                    <td class="py-2">{{ $req->service->name ?? '-' }}</td>
                    <td class="py-2">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                            @if($req->status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($req->status === 'in_progress') bg-blue-100 text-blue-800
                            @elseif($req->status === 'completed') bg-green-100 text-green-800
                            @elseif($req->status === 'rejected') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-600
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-4 text-gray-400">No requests yet</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</div>

@endsection