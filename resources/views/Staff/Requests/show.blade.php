@extends('layouts.app')

@section('content')

@php
    use App\Support\ServiceRequestStatus;
@endphp

<div class="mb-6">

    <h1 class="text-2xl font-bold">
        Request Processing
    </h1>

    <p class="text-sm text-gray-500">
        {{ $request->reference_number }}
        @if($request->office)
            · {{ $request->office->name }}
        @endif
    </p>

</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 space-y-6">

        <div class="bg-white p-6 rounded-xl shadow">

            <p><strong>Citizen:</strong> {{ $request->user->name }}</p>
            <p><strong>Service:</strong> {{ $request->service->name }}</p>
            <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $request->status)) }}</p>
            <p><strong>Assigned:</strong> {{ $request->assignedStaff->name ?? 'Unassigned' }}</p>

        </div>

        <div class="bg-white p-6 rounded-xl shadow">

            <h2 class="font-bold mb-2">Citizen Notes</h2>
            <p>{{ $request->citizen_notes ?? '-' }}</p>

        </div>

        <div class="bg-white p-6 rounded-xl shadow">

            <h2 class="font-bold mb-4">Response Documents</h2>

            @if($request->documents && $request->documents->count())

                <div class="mb-4 space-y-2">
                    @foreach($request->documents as $doc)
                        <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">
                                        {{ $doc->file_name }}
                                    </p>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ $doc->document_type }}
                                        · {{ str_replace('_', ' ', $doc->purpose) }}
                                    </p>
                                </div>
                                <a href="{{ Storage::url($doc->file_path) }}"
                                   target="_blank"
                                   class="text-blue-600 text-sm hover:underline">
                                    View
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

            @else
                <p class="text-sm text-gray-400 mb-4">No documents uploaded yet</p>
            @endif

        </div>

    </div>

    <div class="space-y-6">

        <div class="bg-white p-6 rounded-xl shadow">

            <h2 class="font-bold mb-4">Assign Staff</h2>

            <form method="POST" action="{{ route('staff.requests.assignStaff', $request->id) }}">
                @csrf

                <select name="staff_id" class="w-full border rounded px-3 py-2 mb-3 text-sm" required>
                    <option value="">Select staff member</option>
                    @foreach($officeStaff as $member)
                        <option value="{{ $member->id }}" @selected($request->assigned_staff_id === $member->id)>
                            {{ $member->name }}
                        </option>
                    @endforeach
                </select>

                <button class="w-full bg-indigo-600 text-white py-2 rounded text-sm">
                    Assign
                </button>
            </form>

        </div>

        <div class="bg-white p-6 rounded-xl shadow">

            <h2 class="font-bold mb-4">Update Status</h2>

            <form method="POST" action="{{ route('staff.requests.updateStatus', $request->id) }}">
                @csrf

                <select name="status" class="w-full border rounded px-3 py-2 mb-3 text-sm">
                    @foreach(ServiceRequestStatus::staffUpdatable() as $status)
                        <option value="{{ $status }}" @selected($request->status === $status)>
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </option>
                    @endforeach
                </select>

                <textarea name="staff_notes"
                    placeholder="Notes for the citizen..."
                    class="w-full border rounded px-3 py-2 mb-3 text-sm">{{ old('staff_notes', $request->staff_notes) }}</textarea>

                <textarea name="rejection_reason"
                    placeholder="Rejection reason (required when rejecting)"
                    class="w-full border rounded px-3 py-2 mb-3 text-sm">{{ old('rejection_reason', $request->rejection_reason) }}</textarea>

                <button class="w-full bg-blue-600 text-white py-2 rounded text-sm">
                    Update Status
                </button>
            </form>

        </div>

        <div class="bg-white p-6 rounded-xl shadow">

            <h2 class="font-bold mb-4">Upload Official Document</h2>

            <form method="POST"
                  action="{{ route('staff.requests.uploadDocument', $request->id) }}"
                  enctype="multipart/form-data">

                @csrf

                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">Document Type</label>
                    <select name="document_type" class="w-full border rounded px-3 py-2 text-sm">
                        <option value="response">Official response</option>
                        <option value="certificate">Certificate</option>
                        <option value="receipt">Receipt</option>
                        <option value="other">Other</option>
                    </select>
                    @error('document_type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">
                        File (PDF, JPG, PNG — max 5MB)
                    </label>
                    <input type="file"
                           name="document"
                           accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full border rounded px-3 py-2 text-sm"
                           required>
                    @error('document')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button class="w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700 text-sm">
                    Upload Document
                </button>
            </form>

        </div>

    </div>

</div>

@endsection
