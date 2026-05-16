@extends('layouts.app')

@section('content')

<div class="mb-6">

    <h1 class="text-2xl font-bold">
        Analytics Dashboard
    </h1>

    <p class="text-sm text-gray-500">
        System overview and performance metrics
    </p>

</div>

{{-- STATS GRID --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

    <div class="bg-white p-6 rounded-xl shadow">
        <p class="text-sm text-gray-500">Total Requests</p>
        <h2 class="text-2xl font-bold">{{ $totalRequests }}</h2>
    </div>

    <div class="bg-white p-6 rounded-xl shadow">
        <p class="text-sm text-gray-500">Completed Requests</p>
        <h2 class="text-2xl font-bold text-green-600">{{ $completedRequests }}</h2>
    </div>

    <div class="bg-white p-6 rounded-xl shadow">
        <p class="text-sm text-gray-500">Pending Requests</p>
        <h2 class="text-2xl font-bold text-yellow-600">{{ $pendingRequests }}</h2>
    </div>

</div>

{{-- USERS + SERVICES --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

    <div class="bg-white p-6 rounded-xl shadow">
        <p class="text-sm text-gray-500">Total Users</p>
        <h2 class="text-2xl font-bold">{{ $totalUsers }}</h2>
        <p class="text-xs text-gray-400">Staff: {{ $staffCount }}</p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow">
        <p class="text-sm text-gray-500">Services</p>
        <h2 class="text-2xl font-bold">{{ $totalServices }}</h2>
    </div>

</div>

{{-- PAYMENTS --}}
<div class="bg-white p-6 rounded-xl shadow">

    <h2 class="text-lg font-bold mb-4">Revenue Overview</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div>
            <p class="text-sm text-gray-500">Total Revenue</p>
            <h3 class="text-xl font-bold text-green-600">
                ${{ number_format($totalRevenue, 2) }}
            </h3>
        </div>

        <div>
            <p class="text-sm text-gray-500">Paid Payments</p>
            <h3 class="text-xl font-bold">{{ $paidPayments }}</h3>
        </div>

        <div>
            <p class="text-sm text-gray-500">Pending Payments</p>
            <h3 class="text-xl font-bold text-yellow-600">{{ $pendingPayments }}</h3>
        </div>

    </div>

</div>

@endsection