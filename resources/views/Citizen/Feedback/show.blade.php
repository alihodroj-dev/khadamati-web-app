@extends('layouts.app')

@section('title', 'Your Feedback')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
    <div class="mb-6">
        <a href="{{ route('citizen.requests.show', $feedback->serviceRequest->id) }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
            <i class="ti ti-arrow-left"></i>
            <span>Back to Request</span>
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden" style="border: 0.5px solid #e5e7eb;">
        
        <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-5 text-white">
            <div class="flex items-center gap-2">
                <i class="ti ti-star-filled text-yellow-400 text-2xl"></i>
                <h1 class="text-xl font-bold">Thank You for Your Feedback!</h1>
            </div>
            <p class="text-green-100 text-sm mt-1">Your opinion matters to us</p>
        </div>
        
        <div class="p-6">
            
            <!-- Service Info -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <p class="text-sm text-gray-500">Service</p>
                <p class="font-semibold">{{ $feedback->serviceRequest->service->name }}</p>
                <p class="text-sm text-gray-500 mt-2">Request Reference</p>
                <p class="font-mono text-sm">{{ $feedback->serviceRequest->reference_number }}</p>
            </div>
            
            <!-- Rating Display -->
            <div class="mb-6 text-center">
                <div class="flex justify-center gap-1 text-2xl mb-2">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= $feedback->rating)
                            <i class="ti ti-star-filled text-yellow-400"></i>
                        @else
                            <i class="ti ti-star text-gray-300"></i>
                        @endif
                    @endfor
                </div>
                <p class="text-sm text-gray-500">
                    {{ $feedback->rating == 5 ? 'Excellent!' : ($feedback->rating >= 4 ? 'Very Good' : ($feedback->rating >= 3 ? 'Good' : ($feedback->rating >= 2 ? 'Fair' : 'Poor'))) }}
                </p>
            </div>
            
            <!-- Comment -->
            @if($feedback->comment)
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-900 mb-2">Your Comments</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-700">{{ $feedback->comment }}</p>
                    </div>
                </div>
            @endif
                
            <!-- Staff Response -->
            @if($feedback->responses && $feedback->responses->count() > 0)
                <div class="mt-6 bg-blue-50 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-800 mb-2 flex items-center gap-2">
                        <i class="ti ti-message-circle"></i>
                        Staff Response
                    </h3>
                    @foreach($feedback->responses as $response)
                        <p class="text-blue-700 text-sm">{{ $response->message }}</p>
                        <p class="text-xs text-blue-500 mt-1">Responded: {{ $response->created_at->format('M d, Y h:i A') }}</p>
                    @endforeach
                </div>
            @endif
            
            <!-- Edit Button (only if no staff response yet) -->
            @if($feedback->responses->count() == 0)
                <div class="mt-6">
                    <a href="{{ route('citizen.feedback.edit', $feedback->id) }}" 
                       class="inline-block px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        Edit Feedback
                    </a>
                </div>
            @endif
            
        </div>
    </div>
</div>
@endsection