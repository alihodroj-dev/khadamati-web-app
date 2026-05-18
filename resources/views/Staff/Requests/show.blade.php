@extends('layouts.app')

@section('content')

@php
    use App\Support\ServiceRequestStatus;
@endphp

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Request Details</h1>
    <p class="text-sm text-gray-500">Reference: {{ $request->reference_number }}</p>
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
                <p><span class="font-semibold">Status:</span> 
                    <span class="px-2 py-1 rounded-full text-xs
                        @if($request->status == 'pending') bg-yellow-100 text-yellow-800
                        @elseif($request->status == 'under_review') bg-blue-100 text-blue-800
                        @elseif($request->status == 'approved') bg-green-100 text-green-800
                        @elseif($request->status == 'completed') bg-emerald-100 text-emerald-800
                        @elseif($request->status == 'rejected') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                    </span>
                </p>
                <p><span class="font-semibold">Submitted At:</span> {{ $request->submitted_at ?? $request->created_at }}</p>
            </div>
        </div>

        {{-- CITIZEN NOTES --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <h2 class="font-bold mb-2">Citizen Notes</h2>
            <p class="text-gray-700">{{ $request->citizen_notes ?? 'No notes provided' }}</p>
        </div>

        {{-- STAFF NOTES / RESPONSE --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <h2 class="font-bold mb-4">Add Response</h2>
            <form method="POST" action="{{ route('staff.requests.status', $request->id) }}">
                @csrf
                <textarea name="staff_notes" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm mb-3" placeholder="Add your response or notes for the citizen..."></textarea>
                <button class="w-full bg-blue-600 text-white py-2 rounded-lg text-sm">Save Response</button>
            </form>
        </div>

        {{-- UPLOAD DOCUMENT --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <h2 class="font-bold mb-4">Upload Official Document</h2>
            <form method="POST" action="{{ route('staff.requests.upload', $request->id) }}" enctype="multipart/form-data">
                @csrf
                <select name="document_type" class="w-full border rounded-lg px-3 py-2 mb-3 text-sm" required>
                    <option value="">Select Document Type</option>
                    <option value="response">Official Response</option>
                    <option value="certificate">Certificate</option>
                    <option value="receipt">Receipt</option>
                    <option value="approval">Approval Letter</option>
                </select>
                <input type="file" name="document" class="w-full border rounded-lg px-3 py-2 mb-3 text-sm" required>
                <button class="w-full bg-blue-600 text-white py-2 rounded-lg text-sm">Upload Document</button>
            </form>
        </div>

    </div>

    {{-- RIGHT SIDE ACTION PANEL --}}
    <div class="space-y-6">

        {{-- UPDATE STATUS --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <h2 class="font-bold mb-4">Update Status</h2>
            <form method="POST" action="{{ route('staff.requests.status', $request->id) }}">
                @csrf
                <select name="status" class="w-full border rounded-lg px-3 py-2 mb-3 text-sm">
                    @foreach(ServiceRequestStatus::staffUpdatable() as $status)
                        <option value="{{ $status }}" @selected($request->status === $status)>
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </option>
                    @endforeach
                </select>
                <input type="text" name="rejection_reason" placeholder="Rejection reason (if rejecting)" class="w-full border rounded-lg px-3 py-2 mb-3 text-sm">
                <button class="w-full bg-green-600 text-white py-2 rounded-lg text-sm">Update Status</button>
            </form>
        </div>

        {{-- REJECTION REASON DISPLAY --}}
        @if($request->rejection_reason)
            <div class="bg-red-50 p-4 rounded-xl border border-red-200">
                <p class="font-bold text-red-700 mb-1">Rejection Reason:</p>
                <p class="text-red-600 text-sm">{{ $request->rejection_reason }}</p>
            </div>
        @endif

        {{-- QUICK INFO --}}
        <div class="bg-gray-50 p-4 rounded-xl border text-sm text-gray-600">
            <p><strong>Reference:</strong> {{ $request->reference_number }}</p>
            <p><strong>Tracking Token:</strong> {{ substr($request->tracking_token, 0, 20) }}...</p>
        </div>

    </div>

</div>

@endsection