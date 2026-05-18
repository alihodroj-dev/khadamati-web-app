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
            
            <!-- Documents -->
            @if($request->documents && $request->documents->count() > 0)
            <div class="bg-white rounded-xl p-6 shadow-sm" style="border: 0.5px solid #e5e7eb;">
                <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="ti ti-folder"></i>
                    Documents
                </h2>
                <div class="space-y-2">
                    @foreach($request->documents as $document)
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
                <img src="{{ $qrCodeUrl }}" alt="QR Code" class="mx-auto mb-3" style="width: 150px; height: 150px;">
                <p class="text-xs text-gray-500">Scan to track this request</p>
                <a href="{{ $trackingUrl }}" target="_blank" class="inline-block mt-3 text-sm text-blue-600 hover:underline">
                    Tracking Link →
                </a>
            </div>
            @endif
            
            <!-- Payment Info WITH PAY NOW BUTTON -->
            <div class="bg-white rounded-xl p-6 shadow-sm" style="border: 0.5px solid #e5e7eb;">
                <h2 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <i class="ti ti-credit-card"></i>
                    Payment
                </h2>
                
                @if($request->payment)
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Amount:</span>
                            <span class="font-bold text-gray-900">${{ number_format($request->payment->amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Status:</span>
                            <span class="{{ $request->payment->status == 'paid' ? 'text-green-600' : 'text-red-600' }}">
                                {{ ucfirst($request->payment->status) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Method:</span>
                            <span>{{ ucfirst($request->payment->payment_method ?? 'Not specified') }}</span>
                        </div>
                        
                        @if($request->payment->status != 'paid')
                            <a href="{{ route('citizen.payments.checkout', $request->id) }}" 
                               class="block text-center mt-3 px-3 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition">
                                💳 Pay Now →
                            </a>
                        @endif
                    </div>
                @else
                    <p class="text-gray-500 text-sm mb-3">No payment record found.</p>
                    <a href="{{ route('citizen.payments.checkout', $request->id) }}" 
                       class="block text-center px-3 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition">
                        💳 Proceed to Payment →
                    </a>
                @endif
            </div>
            
            <!-- Appointment Info -->
            @if($request->appointment)
            <div class="bg-white rounded-xl p-6 shadow-sm" style="border: 0.5px solid #e5e7eb;">
                <h2 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <i class="ti ti-calendar"></i>
                    Appointment
                </h2>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Date:</span>
                        <span>{{ \Carbon\Carbon::parse($request->appointment->appointment_date)->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Time:</span>
                        <span>{{ \Carbon\Carbon::parse($request->appointment->appointment_time)->format('h:i A') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status:</span>
                        <span>{{ ucfirst($request->appointment->status) }}</span>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Give Feedback -->
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