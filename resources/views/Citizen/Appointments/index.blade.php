@extends('layouts.app')

@section('title', 'My Appointments')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">My Appointments</h1>
        <p class="text-gray-500 mt-1">View and manage your scheduled appointments</p>
    </div>

    @if($appointments->count() > 0)
        <div class="space-y-4">
            @foreach($appointments as $appointment)
                <div class="bg-white rounded-xl p-5 shadow-sm hover:shadow-md transition" style="border: 0.5px solid #e5e7eb;">
                    
                    <div class="flex flex-wrap justify-between items-start gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2 flex-wrap">
                                <h3 class="font-semibold text-gray-900">{{ $appointment->serviceRequest->service->name ?? 'Service Appointment' }}</h3>
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($appointment->status == 'scheduled') bg-yellow-100 text-yellow-800
                                    @elseif($appointment->status == 'confirmed') bg-green-100 text-green-800
                                    @elseif($appointment->status == 'completed') bg-emerald-100 text-emerald-800
                                    @elseif($appointment->status == 'cancelled') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($appointment->status) }}
                                </span>
                            </div>
                            
                            <div class="flex items-center gap-4 text-sm text-gray-500 mb-1">
                                <span><i class="ti ti-calendar"></i> {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}</span>
                                <span><i class="ti ti-clock"></i> {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</span>
                            </div>
                            
                            <p class="text-sm text-gray-500">
                                <i class="ti ti-user"></i> With: {{ $appointment->staff->name ?? 'Staff Member' }}
                            </p>
                            
                            <p class="text-sm text-gray-500">
                                <i class="ti ti-file-description"></i> Request: {{ $appointment->serviceRequest->reference_number }}
                            </p>
                            
                            @if($appointment->notes)
                                <p class="text-sm text-gray-500 mt-1">
                                    <i class="ti ti-notes"></i> {{ \Str::limit($appointment->notes, 50) }}
                                </p>
                            @endif
                        </div>
                        
                        <div class="flex gap-2">
                            <a href="{{ route('citizen.appointments.show', $appointment->id) }}" 
                               class="px-3 py-1.5 text-sm bg-blue-900 text-white rounded-lg hover:bg-blue-800 transition">
                                View Details
                            </a>
                            
                            @if(in_array($appointment->status, ['scheduled', 'confirmed']))
                                <form method="POST" action="{{ route('citizen.appointments.cancel', $appointment->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="px-3 py-1.5 text-sm border border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition"
                                            onclick="return confirm('Are you sure you want to cancel this appointment?')">
                                        Cancel
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    
                </div>
            @endforeach
        </div>
        
        <div class="mt-6">
            {{ $appointments->links() }}
        </div>
        
    @else
        <div class="bg-white rounded-xl p-12 text-center" style="border: 0.5px solid #e5e7eb;">
            <i class="ti ti-calendar text-gray-300 text-5xl mb-3 block"></i>
            <p class="text-gray-500 mb-2">No appointments yet</p>
            <p class="text-sm text-gray-400">Book an appointment when submitting a service request</p>
            <a href="{{ route('citizen.services.index') }}" class="inline-block mt-3 text-blue-600 hover:underline">
                Browse Services →
            </a>
        </div>
    @endif

</div>
@endsection