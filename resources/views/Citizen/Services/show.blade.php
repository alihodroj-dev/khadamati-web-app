@extends('layouts.app')

@section('title', $service->name)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('citizen.services.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
            <i class="ti ti-arrow-left"></i>
            <span>Back to Services</span>
        </a>
    </div>

    <!-- Service Header -->
    <div class="bg-white rounded-xl p-6 shadow-sm mb-6" style="border: 0.5px solid #e5e7eb;">
        <div class="flex items-start justify-between">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">
                        {{ $service->category->name ?? 'General' }}
                    </span>
                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">
                        Available
                    </span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $service->name }}</h1>
                <p class="text-gray-600">{{ $service->description }}</p>
            </div>
            <div class="text-right">
                <span class="text-3xl font-bold text-blue-900">${{ number_format($service->base_fee, 2) }}</span>
                @if($service->estimated_processing_days)
                    <p class="text-sm text-gray-500 mt-1">
                        <i class="ti ti-clock"></i> ~{{ $service->estimated_processing_days }} days
                    </p>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Main Content (2/3) -->
        <div class="md:col-span-2 space-y-6">
            
            <!-- Office Information -->
            @if($service->office)
            <div class="bg-white rounded-xl p-6 shadow-sm" style="border: 0.5px solid #e5e7eb;">
                <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="ti ti-building"></i>
                    Service Office
                </h2>
                <div class="space-y-2">
                    <p class="text-gray-700"><strong>Office:</strong> {{ $service->office->name }}</p>
                    <p class="text-gray-700"><strong>Address:</strong> {{ $service->office->address ?? 'Not specified' }}</p>
                    @if($service->office->phone)
                        <p class="text-gray-700"><strong>Phone:</strong> {{ $service->office->phone }}</p>
                    @endif
                    @if($service->office->email)
                        <p class="text-gray-700"><strong>Email:</strong> {{ $service->office->email }}</p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Required Documents -->
            <div class="bg-white rounded-xl p-6 shadow-sm" style="border: 0.5px solid #e5e7eb;">
                <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="ti ti-file-description"></i>
                    Required Documents
                </h2>
                @php
                    $documents = is_array($service->required_documents) ? $service->required_documents : json_decode($service->required_documents ?? '[]', true);
                @endphp
                @if(!empty($documents) && count($documents) > 0)
                    <ul class="space-y-2">
                        @foreach($documents as $doc)
                            <li class="flex items-center gap-2 text-gray-700">
                                <i class="ti ti-file-text text-gray-400"></i>
                                <span>{{ $doc['label'] ?? $doc }}</span>
                                @if(isset($doc['required']) && $doc['required'])
                                    <span class="text-xs text-red-500">(Required)</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-500">No specific documents required for this service.</p>
                @endif
            </div>

            <!-- Service Features -->
            <div class="bg-white rounded-xl p-6 shadow-sm" style="border: 0.5px solid #e5e7eb;">
                <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="ti ti-info-circle"></i>
                    Service Information
                </h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Processing Time</p>
                        <p class="font-medium">{{ $service->estimated_processing_days ?? 'Varies' }} days</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Appointment Required</p>
                        <p class="font-medium">{{ $service->requires_appointment ? 'Yes' : 'No' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Service Fee</p>
                        <p class="font-medium">${{ number_format($service->base_fee, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="font-medium text-green-600">Active</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar (1/3) -->
        <div class="space-y-6">
            
            <!-- Request Card -->
            <div class="bg-gradient-to-r from-blue-900 to-blue-800 rounded-xl p-6 text-white text-center sticky top-6">
                <i class="ti ti-file-plus text-3xl mb-3 block"></i>
                <h3 class="font-semibold text-lg mb-2">Ready to apply?</h3>
                <p class="text-sm text-white/80 mb-4">Submit a request for this service</p>
                <a href="{{ route('citizen.services.request.create', $service->id) }}" 
                   class="block w-full bg-white text-blue-900 py-2 rounded-lg font-medium hover:bg-gray-100 transition">
                    Start Request →
                </a>
            </div>

            <!-- Need Help -->
            <div class="bg-white rounded-xl p-5 shadow-sm text-center" style="border: 0.5px solid #e5e7eb;">
                <i class="ti ti-headset text-2xl text-gray-400 mb-2 block"></i>
                <h3 class="font-medium text-gray-900 mb-1">Need Help?</h3>
                <p class="text-xs text-gray-500 mb-3">Contact support for assistance</p>
                <a href="#" class="text-sm text-blue-600 hover:underline">Contact Support →</a>
            </div>
        </div>
    </div>
</div>
@endsection