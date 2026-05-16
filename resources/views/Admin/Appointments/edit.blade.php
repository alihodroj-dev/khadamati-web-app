@extends('layouts.app')

@section('content')

<div class="mb-6">

    <h1 class="text-2xl font-bold">
        Edit Appointment
    </h1>

    <p class="text-sm text-gray-500">
        Update scheduling details
    </p>

</div>

<form method="POST"
      action="{{ route('admin.appointments.update', $appointment->id) }}">

    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- LEFT --}}
        <div class="bg-white p-6 rounded-xl shadow space-y-4">

            <div>
                <label class="text-sm font-medium">Date</label>
                <input type="date"
                       name="appointment_date"
                       value="{{ $appointment->appointment_date }}"
                       class="w-full border rounded px-3 py-2 mt-1">
            </div>

            <div>
                <label class="text-sm font-medium">Time</label>
                <input type="time"
                       name="appointment_time"
                       value="{{ $appointment->appointment_time }}"
                       class="w-full border rounded px-3 py-2 mt-1">
            </div>

            <div>
                <label class="text-sm font-medium">Status</label>

                <select name="status"
                        class="w-full border rounded px-3 py-2 mt-1">

                    <option value="scheduled" {{ $appointment->status == 'scheduled' ? 'selected' : '' }}>
                        Scheduled
                    </option>

                    <option value="completed" {{ $appointment->status == 'completed' ? 'selected' : '' }}>
                        Completed
                    </option>

                    <option value="cancelled" {{ $appointment->status == 'cancelled' ? 'selected' : '' }}>
                        Cancelled
                    </option>

                </select>

            </div>

        </div>

        {{-- RIGHT --}}
        <div class="bg-white p-6 rounded-xl shadow space-y-4">

            <div>
                <label class="text-sm font-medium">Notes</label>

                <textarea name="notes"
                          class="w-full border rounded px-3 py-2 mt-1"
                          rows="6">{{ $appointment->notes }}</textarea>

            </div>

        </div>

    </div>

    {{-- ACTIONS --}}
    <div class="mt-6 flex gap-3">

        <button class="bg-blue-600 text-white px-6 py-2 rounded-lg">
            Save Changes
        </button>

        <a href="{{ route('admin.appointments.index') }}"
           class="px-6 py-2 rounded-lg border">
            Cancel
        </a>

    </div>

</form>

@endsection