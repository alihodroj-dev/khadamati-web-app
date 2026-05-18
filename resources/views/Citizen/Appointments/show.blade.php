@extends('layouts.app')

@section('title', 'Appointment Details')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
    <div class="mb-6">
        <a href="{{ route('citizen.appointments.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
            <i class="ti ti-arrow-left"></i>
            <span>Back to Appointments</span>
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden" style="border: 0.5px solid #e5e7eb;">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-6 text-white">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold">Appointment Details</h1>
                    <p class="text-blue-100 mt-1">{{ $appointment->serviceRequest->service->name ?? 'Service Appointment' }}</p>
                </div>
                <span class="px-3 py-1 text-sm rounded-full
                    @if($appointment->status == 'scheduled') bg-yellow-500 text-white
                    @elseif($appointment->status == 'confirmed') bg-green-500 text-white
                    @elseif($appointment->status == 'completed') bg-emerald-500 text-white
                    @elseif($appointment->status == 'cancelled') bg-red-500 text-white
                    @else bg-gray-500 text-white
                    @endif">
                    {{ ucfirst($appointment->status) }}
                </span>
            </div>
        </div>
        
        <!-- Content -->
        <div class="p-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Left Column -->
                <div>
                    <h3 class="font-semibold text-gray-900 mb-3">Appointment Information</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Date:</span>
                            <span class="font-medium">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('l, F d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Time:</span>
                            <span class="font-medium">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Staff Member:</span>
                            <span class="font-medium">{{ $appointment->staff->name ?? 'Not assigned' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Status:</span>
                            <span class="font-medium">{{ ucfirst($appointment->status) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Booked On:</span>
                            <span>{{ $appointment->created_at->format('M d, Y h:i A') }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div>
                    <h3 class="font-semibold text-gray-900 mb-3">Service Request</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Reference:</span>
                            <a href="{{ route('citizen.requests.show', $appointment->serviceRequest->id) }}" class="text-blue-600 hover:underline">
                                {{ $appointment->serviceRequest->reference_number }}
                            </a>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Service:</span>
                            <span>{{ $appointment->serviceRequest->service->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Request Status:</span>
                            <span>{{ ucfirst($appointment->serviceRequest->status) }}</span>
                        </div>
                    </div>
                </div>
                
            </div>
            
            <!-- Notes -->
            @if($appointment->notes)
            <div class="mt-6 pt-6 border-t">
                <h3 class="font-semibold text-gray-900 mb-2">Additional Notes</h3>
                <p class="text-gray-600 text-sm">{{ $appointment->notes }}</p>
            </div>
            @endif
            
            <!-- Action Buttons -->
            <div class="flex gap-3 mt-8 pt-6 border-t">
                <a href="{{ route('citizen.requests.show', $appointment->serviceRequest->id) }}" 
                   class="flex-1 text-center px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800 transition">
                    View Service Request
                </a>
                
                @if(in_array($appointment->status, ['scheduled', 'confirmed']))
                    <form method="POST" action="{{ route('citizen.appointments.cancel', $appointment->id) }}" class="flex-1">
                        @csrf
                        <button type="submit" 
                                class="w-full px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition"
                                onclick="return confirm('Are you sure you want to cancel this appointment?')">
                            Cancel Appointment
                        </button>
                    </form>
                @endif
            </div>
            
        </div>
        
    </div>
</div>
@endsection