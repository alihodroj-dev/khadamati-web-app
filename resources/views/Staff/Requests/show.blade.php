@extends('layouts.app')

@section('content')

<div class="mb-6">

    <h1 class="text-2xl font-bold">
        Request Processing
    </h1>

    <p class="text-sm text-gray-500">
        {{ $request->reference_number }}
    </p>

</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT --}}
    <div class="lg:col-span-2 space-y-6">

        <div class="bg-white p-6 rounded-xl shadow">

            <p><strong>Citizen:</strong> {{ $request->user->name }}</p>
            <p><strong>Service:</strong> {{ $request->service->name }}</p>
            <p><strong>Status:</strong> {{ $request->status }}</p>

        </div>

        <div class="bg-white p-6 rounded-xl shadow">

            <h2 class="font-bold mb-2">Citizen Notes</h2>
            <p>{{ $request->citizen_notes ?? '-' }}</p>

        </div>

    </div>

    {{-- RIGHT ACTIONS --}}
    <div class="space-y-6">

        <div class="bg-white p-6 rounded-xl shadow">

            <h2 class="font-bold mb-4">Update Request</h2>

            <form method="POST"
                  action="{{ route('staff.requests.updateStatus', $request->id) }}">

                @csrf

                <select name="status" class="w-full border rounded px-3 py-2 mb-3">
                    <option value="in_progress" {{ $request->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ $request->status === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="rejected" {{ $request->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>

                <textarea name="staff_notes" class="w-full border rounded px-3 py-2 mb-3">{{ $request->staff_notes }}</textarea>

                <button class="w-full bg-blue-600 text-white py-2 rounded">
                    Update
                </button>

            </form>

        </div>

    </div>

</div>

@endsection