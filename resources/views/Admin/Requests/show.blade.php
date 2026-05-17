@extends('layouts.app')

@section('content')

@php
    use App\Support\ServiceRequestStatus;
@endphp

<div class="mb-6">

    <h1 class="text-2xl font-bold text-gray-900">
        Request Details
    </h1>

    <p class="text-sm text-gray-500">
        Reference: {{ $request->reference_number }}
    </p>

</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT SIDE --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- BASIC INFO --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border">

            <h2 class="font-bold text-lg mb-4">Request Information</h2>

            <div class="space-y-2 text-sm">

                <p><span class="font-semibold">Citizen:</span> {{ $request->user->name }}</p>

                <p><span class="font-semibold">Service:</span> {{ $request->service->name }}</p>

                <p>
                    <span class="font-semibold">Status:</span>
                    {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                </p>

                <p><span class="font-semibold">Assigned Staff:</span> {{ $request->assignedStaff->name ?? 'Not assigned' }}</p>

                <p><span class="font-semibold">Submitted At:</span> {{ $request->submitted_at ?? $request->created_at }}</p>

            </div>

        </div>

        {{-- CITIZEN NOTES --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border">

            <h2 class="font-bold mb-2">Citizen Notes</h2>

            <p class="text-gray-700">
                {{ $request->citizen_notes ?? 'No notes provided' }}
            </p>

        </div>

        {{-- STAFF NOTES --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border">

            <h2 class="font-bold mb-2">Staff Notes</h2>

            <p class="text-gray-700">
                {{ $request->staff_notes ?? 'No staff notes yet' }}
            </p>

        </div>

        {{-- REJECTION REASON --}}
        @if($request->rejection_reason)

            <div class="bg-red-50 p-6 rounded-xl border border-red-200">

                <h2 class="font-bold mb-2 text-red-700">Rejection Reason</h2>

                <p class="text-red-700">
                    {{ $request->rejection_reason }}
                </p>

            </div>

        @endif

    </div>

    {{-- RIGHT SIDE ACTION PANEL --}}
    <div class="space-y-6">

        {{-- ASSIGN STAFF --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border">

            <h2 class="font-bold mb-4">Assign Staff</h2>

            <form method="POST"
                  action="{{ route('admin.requests.assignStaff', $request->id) }}">

                @csrf

                <select name="staff_id"
                        class="w-full border rounded-lg px-3 py-2 mb-3 text-sm">

                    <option value="">Select Staff</option>

                    @foreach($staff as $member)

                        <option value="{{ $member->id }}" @selected($request->assigned_staff_id === $member->id)>
                            {{ $member->name }}
                        </option>

                    @endforeach

                </select>

                <button class="w-full bg-blue-600 text-white py-2 rounded-lg text-sm">
                    Assign Staff
                </button>

            </form>

        </div>

        {{-- UPDATE STATUS --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border">

            <h2 class="font-bold mb-4">Update Status</h2>

            <form method="POST"
                  action="{{ route('admin.requests.updateStatus', $request->id) }}">

                @csrf

                <select name="status"
                        class="w-full border rounded-lg px-3 py-2 mb-3 text-sm">

                    @foreach(ServiceRequestStatus::adminUpdatable() as $status)
                        <option value="{{ $status }}" @selected($request->status === $status)>
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </option>
                    @endforeach

                </select>

                <input type="text"
                       name="rejection_reason"
                       placeholder="Rejection reason (if any)"
                       class="w-full border rounded-lg px-3 py-2 mb-3 text-sm">

                <button class="w-full bg-green-600 text-white py-2 rounded-lg text-sm">
                    Update Status
                </button>

            </form>

        </div>

        {{-- QUICK INFO --}}
        <div class="bg-gray-50 p-4 rounded-xl border text-sm text-gray-600">

            <p><strong>Reference:</strong> {{ $request->reference_number }}</p>
            <p><strong>ID:</strong> {{ $request->id }}</p>

        </div>

    </div>

</div>

@endsection