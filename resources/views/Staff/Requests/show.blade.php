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

            <h2 class="font-bold mb-4">Respond to Request</h2>

            <form method="POST"
                  action="{{ route('staff.requests.updateStatus', $request->id) }}">

                @csrf

                <select name="status" class="w-full border rounded px-3 py-2 mb-3">
                    <option value="in_progress" {{ $request->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ $request->status === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="rejected" {{ $request->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>

                <textarea name="staff_notes"
                    placeholder="Write your response to the citizen..."
                    class="w-full border rounded px-3 py-2 mb-3">{{ $request->staff_notes }}</textarea>

                <button class="w-full bg-blue-600 text-white py-2 rounded">
                    Update
                </button>

            </form>

            {{-- DOCUMENT UPLOAD --}}
            <div class="bg-white p-6 rounded-xl shadow">

                <h2 class="font-bold mb-4">Response Documents</h2>

                {{-- Existing documents --}}
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
                                            · {{ number_format($doc->file_size / 1024, 1) }} KB
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <span class="text-xs px-2 py-1 rounded-full
                                            {{ $doc->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                            {{ ucfirst($doc->status) }}
                                        </span>
                                        <a href="{{ Storage::url($doc->file_path) }}"
                                        target="_blank"
                                        class="text-blue-600 text-sm hover:underline">
                                            View
                                        </a>
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>

                @else
                    <p class="text-sm text-gray-400 mb-4">No documents uploaded yet</p>
                @endif

                {{-- Upload form --}}
                <form method="POST"
                    action="{{ route('staff.requests.uploadDocument', $request->id) }}"
                    enctype="multipart/form-data">

                    @csrf

                    {{-- Document Type --}}
                    <div class="mb-3">
                        <label class="block text-sm text-gray-600 mb-1">Document Type</label>
                        <select name="document_type"
                                class="w-full border rounded px-3 py-2 text-sm">
                            <option value="response">Response</option>
                            <option value="certificate">Certificate</option>
                            <option value="receipt">Receipt</option>
                            <option value="approval">Approval</option>
                            <option value="rejection">Rejection</option>
                        </select>
                        @error('document_type')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- File --}}
                    <div class="mb-3">
                        <label class="block text-sm text-gray-600 mb-1">
                            Select File (PDF, JPG, PNG — max 5MB)
                        </label>
                        <input type="file"
                            name="document"
                            accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full border rounded px-3 py-2 text-sm">
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
</div>

@endsection