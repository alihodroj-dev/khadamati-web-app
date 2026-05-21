@extends('layouts.app')

@section('content')

<div class="mb-6">

    <h1 class="text-2xl font-bold">
        Appointment Details
    </h1>

    <p class="text-sm text-gray-500">
        Reference: {{ $appointment->serviceRequest->reference_number ?? '-' }}
    </p>

</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- DETAILS --}}
    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow">

        <p><strong>User:</strong> {{ $appointment->user->name }}</p>
        <p><strong>Staff:</strong> {{ $appointment->staff->name ?? 'Not assigned' }}</p>
        <p><strong>Date:</strong> {{ $appointment->appointment_date }}</p>
        <p><strong>Time:</strong> {{ $appointment->appointment_time }}</p>
        <p><strong>Status:</strong> {{ $appointment->status }}</p>

    </div>

    {{-- NOTES --}}
    <div class="bg-white p-6 rounded-xl shadow">

        <h2 class="font-bold mb-2">Notes</h2>
        <p>{{ $appointment->notes ?? '-' }}</p>

    </div>

</div>

@endsection