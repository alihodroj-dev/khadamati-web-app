@extends('layouts.app')

@section('content')

<div class="mb-6">

    <h1 class="text-2xl font-bold">
        Appointment Details
    </h1>

</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- INFO --}}
    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow">

        <p><strong>User:</strong> {{ $appointment->user->name }}</p>
        <p><strong>Date:</strong> {{ $appointment->appointment_date }}</p>
        <p><strong>Time:</strong> {{ $appointment->appointment_time }}</p>
        <p><strong>Status:</strong> {{ $appointment->status }}</p>

    </div>

    {{-- ACTIONS --}}
    <div class="bg-white p-6 rounded-xl shadow">

        <form method="POST"
              action="{{ route('staff.appointments.update', $appointment->id) }}">

            @csrf

            <select name="status" class="w-full border rounded px-3 py-2 mb-3">
                <option value="completed" {{ $appointment->status === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ $appointment->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                <option value="in_progress" {{ $appointment->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
            </select>

            <textarea name="notes" class="w-full border rounded px-3 py-2 mb-3">{{ $appointment->notes }}</textarea>

            <button class="w-full bg-blue-600 text-white py-2 rounded">
                Update
            </button>

        </form>

    </div>

</div>

@endsection