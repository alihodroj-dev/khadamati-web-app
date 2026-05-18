@extends('layouts.app')

@section('title', 'Request Details - ' . $request->reference_number)

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('citizen.requests.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
            <i class="ti ti-arrow-left"></i>
            <span>Back to My Requests</span>
        </a>
    </div>

    <!-- Header -->
    <div class="bg-white rounded-xl p-6 shadow-sm mb-6" style="border: 0.5px solid #e5e7eb;">
        <div class="flex flex-wrap justify-between items-start gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2 flex-wrap">
                    <span class="text-sm text-gray-500">Request #{{ $request->reference_number }}</span>
                    <span class="px-2 py-1 text-xs rounded-full
                        @if($request->status == 'pending') bg-yellow-100 text-yellow-800
                        @elseif($request->status == 'under_review') bg-blue-100 text-blue-800
                        @elseif($request->status == 'approved') bg-green-100 text-green-800
                        @elseif($request->status == 'completed') bg-emerald-100 text-emerald-800
                        @elseif($request->status == 'rejected') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                    </span>
                </div>
                <h1 class="text-xl font-bold text-gray-900">{{ $request->service->name ?? 'Service Request' }}</h1>
                <p class="text-sm text-gray-500 mt-1">Submitted: {{ $request->submitted_at ? $request->submitted_at->format('M d, Y h:i A') : $request->created_at->format('M d, Y h:i A') }}</p>
            </div>
            
            @if(in_array($request->status, ['pending', 'under_review']))
                <form method="POST" action="{{ route('citizen.requests.cancel', $request->id) }}">
                    @csrf
                    <button type="submit" 
                            class="px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition"
                            onclick="return confirm('Are you sure you want to cancel this request?')">
                        Cancel Request
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Main Content (2/3) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Status Timeline -->
            <div class="bg-white rounded-xl p-6 shadow-sm" style="border: 0.5px solid #e5e7eb;">
                <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="ti ti-progress"></i>
                    Status Timeline
                </h2>
                <div class="relative">
                    <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                    
                    <div class="relative pl-10 pb-6">
                        <div class="absolute left-2 top-1 w-5 h-5 rounded-full bg-green-500 border-2 border-white"></div>
                        <p class="font-medium text-gray-900">Request Submitted</p>
                        <p class="text-sm text-gray-500">{{ $request->submitted_at ? $request->submitted_at->format('M d, Y h:i A') : $request->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    
                    @if(in_array($request->status, ['under_review', 'approved', 'completed', 'rejected']))
                    <div class="relative pl-10 pb-6">
                        <div class="absolute left-2 top-1 w-5 h-5 rounded-full bg-blue-500 border-2 border-white"></div>
                        <p class="font-medium text-gray-900">Under Review</p>
                        <p class="text-sm text-gray-500">{{ $request->reviewed_at ? $request->reviewed_at->format('M d, Y h:i A') : 'In progress' }}</p>
                    </div>
                    @endif
                    
                    @if(in_array($request->status, ['approved', 'completed']))
                    <div class="relative pl-10 pb-6">
                        <div class="absolute left-2 top-1 w-5 h-5 rounded-full bg-green-500 border-2 border-white"></div>
                        <p class="font-medium text-gray-900">Approved</p>
                        <p class="text-sm text-gray-500">Your request has been approved</p>
                    </div>
                    @endif
                    
                    @if($request->status == 'completed')
                    <div class="relative pl-10">
                        <div class="absolute left-2 top-1 w-5 h-5 rounded-full bg-emerald-500 border-2 border-white"></div>
                        <p class="font-medium text-gray-900">Completed</p>
                        <p class="text-sm text-gray-500">{{ $request->completed_at ? $request->completed_at->format('M d, Y h:i A') : 'Completed' }}</p>
                    </div>
                    @endif
                    
                    @if($request->status == 'rejected')
                    <div class="relative pl-10">
                        <div class="absolute left-2 top-1 w-5 h-5 rounded-full bg-red-500 border-2 border-white"></div>
                        <p class="font-medium text-gray-900">Rejected</p>
                        @if($request->rejection_reason)
                            <p class="text-sm text-red-600 mt-1">Reason: {{ $request->rejection_reason }}</p>
                        @endif
                    </div>
                    @endif
                    
                    @if($request->status == 'cancelled')
                    <div class="relative pl-10">
                        <div class="absolute left-2 top-1 w-5 h-5 rounded-full bg-gray-500 border-2 border-white"></div>
                        <p class="font-medium text-gray-900">Cancelled</p>
                        <p class="text-sm text-gray-500">You cancelled this request</p>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Your Notes -->
            @if($request->citizen_notes)
            <div class="bg-white rounded-xl p-6 shadow-sm" style="border: 0.5px solid #e5e7eb;">
                <h2 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <i class="ti ti-notes"></i>
                    Your Notes
                </h2>
                <p class="text-gray-600">{{ $request->citizen_notes }}</p>
            </div>
            @endif
            
            <!-- Staff Notes (if any) -->
            @if($request->staff_notes)
            <div class="bg-blue-50 rounded-xl p-6 shadow-sm" style="border: 0.5px solid #bfdbfe;">
                <h2 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <i class="ti ti-message-circle"></i>
                    Staff Response
                </h2>
                <p class="text-gray-700">{{ $request->staff_notes }}</p>
            </div>
            @endif
            
            <!-- OFFICIAL DOCUMENTS (from staff) -->
            @if($request->documents && $request->documents->where('source', 'staff')->count() > 0)
            <div class="bg-white rounded-xl p-6 shadow-sm" style="border: 0.5px solid #e5e7eb;">
                <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="ti ti-certificate text-blue-600"></i>
                    Official Documents
                </h2>
                <div class="space-y-2">
                    @foreach($request->documents->where('source', 'staff') as $document)
                        <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                            <div class="flex items-center gap-2">
                                <i class="ti ti-file-certificate text-blue-600"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">{{ $document->file_name }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ ucfirst(str_replace('_', ' ', $document->purpose ?? 'document')) }} • 
                                        {{ $document->created_at->format('M d, Y') }}
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('citizen.requests.download-document', ['requestId' => $request->id, 'documentId' => $document->id]) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm">
                                Download <i class="ti ti-download"></i>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            <!-- YOUR UPLOADED DOCUMENTS (from citizen) -->
            @if($request->documents && $request->documents->where('source', 'citizen')->count() > 0)
            <div class="bg-white rounded-xl p-6 shadow-sm" style="border: 0.5px solid #e5e7eb;">
                <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="ti ti-upload"></i>
                    Your Uploaded Documents
                </h2>
                <div class="space-y-2">
                    @foreach($request->documents->where('source', 'citizen') as $document)
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-2">
                                <i class="ti ti-file-text text-gray-500"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">{{ $document->file_name }}</p>
                                    <p class="text-xs text-gray-400">Uploaded: {{ $document->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <a href="{{ route('citizen.requests.download-document', ['requestId' => $request->id, 'documentId' => $document->id]) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm">
                                Download <i class="ti ti-download"></i>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
            
        </div>
        
        <!-- Sidebar (1/3) -->
        <div class="space-y-6">
            
            <!-- QR Code Card -->
            @if($qrCodeUrl)
            <div class="bg-white rounded-xl p-6 shadow-sm text-center" style="border: 0.5px solid #e5e7eb;">
                <h2 class="font-semibold text-gray-900 mb-3">Track with QR Code</h2>
                <img src="{{ route('qr.code', $request->id) }}" alt="QR Code" class="mx-auto mb-3" style="width: 150px; height: 150px;">
                <p class="text-xs text-gray-500">Scan to track this request</p>
                <a href="{{ $trackingUrl }}" target="_blank" class="inline-block mt-3 text-sm text-blue-600 hover:underline">
                    Tracking Link →
                </a>
            </div>
            @endif
            
           <!-- Payment Info WITH PAY NOW BUTTON -->
            <div class="bg-white rounded-xl p-6 shadow-sm mb-4" style="border: 0.5px solid #e5e7eb;">
                <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="ti ti-credit-card text-blue-600"></i>
                    Payment
                </h2>
                
                @if($request->payment)
                    <div class="space-y-3">
                        <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                            <span class="text-gray-500">Amount:</span>
                            <span class="font-bold text-xl text-blue-900">${{ number_format($request->payment->amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Status:</span>
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($request->payment->status == 'paid') bg-green-100 text-green-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst($request->payment->status) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Method:</span>
                            <span class="text-gray-700">{{ ucfirst($request->payment->payment_method ?? 'Not specified') }}</span>
                        </div>
                        
                        @if($request->payment->status != 'paid')
                            <div class="mt-4 pt-2">
                                <a href="{{ route('citizen.payments.checkout', $request->id) }}" 
                                class="block w-full text-center px-4 py-3 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition">
                                    PAY NOW →
                                </a>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-gray-500 text-sm mb-4">No payment record found.</p>
                    <a href="{{ route('citizen.payments.checkout', $request->id) }}" 
                    class="block w-full text-center px-4 py-3 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition">
                        PROCEED TO PAYMENT →
                    </a>
                @endif
            </div>
            
            <!-- Your Feedback Card -->
            @if($request->feedback)
                <div class="bg-white rounded-xl p-5 shadow-sm" style="border: 0.5px solid #e5e7eb;">
                    <h2 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                        <i class="ti ti-star text-yellow-500"></i>
                        Your Feedback
                    </h2>
                    <div class="flex gap-0.5 mb-2">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= $request->feedback->rating)
                                <i class="ti ti-star-filled text-yellow-400 text-sm"></i>
                            @else
                                <i class="ti ti-star text-gray-300 text-sm"></i>
                            @endif
                        @endfor
                    </div>
                    @if($request->feedback->comment)
                        <p class="text-sm text-gray-600 mt-2">{{ \Str::limit($request->feedback->comment, 100) }}</p>
                    @endif
                    <a href="{{ route('citizen.feedback.show', $request->feedback->id) }}" 
                       class="inline-block mt-3 text-blue-600 hover:underline text-sm">
                        View Full Feedback →
                    </a>
                </div>
            @endif
            
            <!-- Book Appointment Card -->
            @if(isset($request->service) && $request->service->requires_appointment && !$request->appointment && !in_array($request->status, ['cancelled', 'rejected', 'completed']))
                <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-xl p-5 text-white text-center">
                    <i class="ti ti-calendar-plus text-3xl mb-2 block"></i>
                    <h3 class="font-semibold text-lg mb-1">Need an Appointment?</h3>
                    <p class="text-sm text-white/80 mb-3">This service requires an in-person visit. Book a time with our staff.</p>
                    <a href="{{ route('citizen.appointments.create', $request->id) }}" 
                    class="inline-block px-4 py-2 bg-white text-green-700 rounded-lg text-sm font-medium hover:bg-gray-100 transition w-full">
                        Book Appointment →
                    </a>
                </div>
            @endif
            
            <!-- Existing Appointment Card -->
            @if($request->appointment)
                <div class="bg-white rounded-xl p-5 shadow-sm" style="border: 0.5px solid #e5e7eb;">
                    <h2 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                        <i class="ti ti-calendar text-green-600"></i>
                        Your Appointment
                    </h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Date:</span>
                            <span class="font-medium">{{ \Carbon\Carbon::parse($request->appointment->appointment_date)->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Time:</span>
                            <span class="font-medium">{{ \Carbon\Carbon::parse($request->appointment->appointment_time)->format('h:i A') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">With:</span>
                            <span class="font-medium">{{ $request->appointment->staff->name ?? 'Staff Member' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Status:</span>
                            <span class="px-2 py-0.5 text-xs rounded-full
                                @if($request->appointment->status == 'scheduled') bg-yellow-100 text-yellow-800
                                @elseif($request->appointment->status == 'confirmed') bg-green-100 text-green-800
                                @elseif($request->appointment->status == 'completed') bg-emerald-100 text-emerald-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($request->appointment->status) }}
                            </span>
                        </div>
                        <a href="{{ route('citizen.appointments.show', $request->appointment->id) }}" 
                           class="block text-center mt-3 text-blue-600 hover:underline text-sm">
                            View Appointment Details →
                        </a>
                    </div>
                </div>
            @endif
            
            <!-- Give Feedback Button -->
            @if($request->status == 'completed' && !$request->feedback)
                <div class="bg-gradient-to-r from-blue-900 to-blue-800 rounded-xl p-6 text-white text-center">
                    <i class="ti ti-star text-2xl mb-2 block"></i>
                    <h3 class="font-semibold text-lg mb-1">Rate this Service</h3>
                    <p class="text-sm text-white/80 mb-3">Share your experience</p>
                    <a href="{{ route('citizen.feedback.create', $request->id) }}" 
                       class="inline-block px-4 py-2 bg-white text-blue-900 rounded-lg text-sm font-medium hover:bg-gray-100 transition">
                        Give Feedback →
                    </a>
                </div>
            @endif
            
        </div>
    </div>
</div>
@endsection