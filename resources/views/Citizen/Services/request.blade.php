@extends('layouts.app')

@section('title', 'Request Service - ' . $service->name)

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('citizen.services.show', $service->id) }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
            <i class="ti ti-arrow-left"></i>
            <span>Back to Service Details</span>
        </a>
    </div>

    <!-- Header -->
    <div class="bg-white rounded-xl p-6 shadow-sm mb-6" style="border: 0.5px solid #e5e7eb;">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <i class="ti ti-file-plus text-blue-600 text-2xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Submit Service Request</h1>
                <p class="text-gray-500 text-sm">{{ $service->name }}</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('citizen.services.request.store', $service->id) }}" enctype="multipart/form-data">
        @csrf

        <div class="bg-white rounded-xl p-6 shadow-sm mb-6" style="border: 0.5px solid #e5e7eb;">
            
            <!-- Service Summary -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500">Service</p>
                        <p class="font-medium">{{ $service->name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Fee</p>
                        <p class="font-bold text-blue-900">${{ number_format($service->base_fee, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Additional Notes -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Additional Notes (Optional)
                </label>
                <textarea name="citizen_notes" rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                          placeholder="Any additional information you'd like to provide...">{{ old('citizen_notes') }}</textarea>
            </div>

            <!-- Document Uploads -->
            @php
                $documents = is_array($service->required_documents) ? $service->required_documents : json_decode($service->required_documents ?? '[]', true);
            @endphp
            
            @if(!empty($documents) && count($documents) > 0)
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Required Documents
                    </label>
                    <div class="space-y-4">
                        @foreach($documents as $index => $doc)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <label class="block text-sm font-medium text-gray-800 mb-2">
                                    {{ $doc['label'] ?? $doc }}
                                    @if(isset($doc['required']) && $doc['required'])
                                        <span class="text-red-500">*</span>
                                    @endif
                                </label>
                                <input type="file" 
                                       name="documents[{{ $doc['key'] ?? 'doc_' . $index }}]" 
                                       accept=".pdf,.jpg,.jpeg,.png"
                                       @if(isset($doc['required']) && $doc['required']) required @endif
                                       class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                <p class="text-xs text-gray-400 mt-1">Accepted formats: PDF, JPG, PNG (Max 5MB)</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Service Terms -->
            <div class="mb-6">
                <label class="flex items-center gap-2">
                    <input type="checkbox" required class="rounded border-gray-300 text-blue-600">
                    <span class="text-sm text-gray-600">
                        I confirm that the information provided is accurate and complete to the best of my knowledge.
                    </span>
                </label>
            </div>

        </div>

        <!-- Submit Buttons -->
        <div class="flex gap-3">
            <a href="{{ route('citizen.services.show', $service->id) }}" 
               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit" 
                    class="flex-1 px-6 py-2 bg-blue-900 text-white rounded-lg font-medium hover:bg-blue-800 transition">
                Submit Request
            </button>
        </div>
    </form>

</div>
@endsection