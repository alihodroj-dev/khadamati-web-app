@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Citizen Feedback</h1>
    <p class="text-sm text-gray-500">Feedback for your office services</p>
</div>

@if($feedbacks->count() > 0)
    <div class="space-y-4">
        @foreach($feedbacks as $feedback)
            <div class="bg-white rounded-xl p-5 shadow-sm border">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="font-semibold">{{ $feedback->user->name }}</span>
                            <span class="text-xs text-gray-400">{{ $feedback->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="flex gap-0.5 mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $feedback->rating)
                                    <i class="ti ti-star-filled text-yellow-400 text-sm"></i>
                                @else
                                    <i class="ti ti-star text-gray-300 text-sm"></i>
                                @endif
                            @endfor
                        </div>
                        @if($feedback->comment)
                            <p class="text-gray-600 text-sm mt-2">{{ $feedback->comment }}</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-2">Service: {{ $feedback->serviceRequest->service->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-4">
        {{ $feedbacks->links() }}
    </div>
@else
    <div class="bg-white rounded-xl p-12 text-center border">
        <i class="ti ti-message-circle text-gray-300 text-5xl mb-3 block"></i>
        <p class="text-gray-500">No feedback yet</p>
    </div>
@endif
@endsection