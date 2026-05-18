@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Feedback Details</h1>
    <p class="text-sm text-gray-500">Review and respond to citizen feedback</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Left Column -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Feedback Card -->
        <div class="bg-white rounded-xl p-6 shadow-sm border">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h2 class="font-bold text-lg">{{ $feedback->user->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $feedback->serviceRequest->service->name ?? 'Service' }}</p>
                </div>
                <div class="flex gap-0.5">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= $feedback->rating)
                            <i class="ti ti-star-filled text-yellow-400 text-lg"></i>
                        @else
                            <i class="ti ti-star text-gray-300 text-lg"></i>
                        @endif
                    @endfor
                </div>
            </div>
            
            @if($feedback->comment)
                <div class="bg-gray-50 rounded-lg p-4 mt-2">
                    <p class="text-gray-700">{{ $feedback->comment }}</p>
                </div>
            @endif
            
            <div class="mt-4 text-sm text-gray-500">
                Submitted: {{ $feedback->created_at->format('M d, Y h:i A') }}
            </div>
        </div>
        
        <!-- Responses -->
        @if($feedback->responses->count() > 0)
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <h2 class="font-bold text-lg mb-4">Staff Responses</h2>
                @foreach($feedback->responses as $response)
                    <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                        <div class="flex justify-between items-start mb-2">
                            <span class="font-medium text-blue-800">{{ $response->responder->name }}</span>
                            <span class="text-xs text-blue-500">{{ $response->created_at->format('M d, Y h:i A') }}</span>
                        </div>
                        <p class="text-blue-700 text-sm">{{ $response->message }}</p>
                        <span class="text-xs text-blue-500 mt-1 inline-block">
                            {{ ucfirst($response->visibility) }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
        
    </div>
    
    <!-- Right Column - Add Response -->
    <div>
        <div class="bg-white rounded-xl p-6 shadow-sm border sticky top-6">
            <h2 class="font-bold text-lg mb-4">Add Response</h2>
            
            <form method="POST" action="{{ route('admin.feedback.respond', $feedback->id) }}">
                @csrf
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Response Message</label>
                    <textarea name="message" rows="4" class="w-full border rounded-lg px-3 py-2 text-sm" required></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Visibility</label>
                    <select name="visibility" class="w-full border rounded-lg px-3 py-2 text-sm">
                        <option value="public">Public (Citizen can see)</option>
                        <option value="private">Private (Admin only)</option>
                    </select>
                </div>
                
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg text-sm hover:bg-blue-700">
                    Send Response
                </button>
            </form>
        </div>
    </div>
    
</div>
@endsection