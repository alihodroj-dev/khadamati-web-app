@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold mb-6">My Conversations</h1>

    <x-card>
        @forelse($conversations as $conversation)
        <a href="{{ route('staff.conversations.show', $conversation->id) }}" 
           class="block border-b border-gray-100 hover:bg-gray-50 transition-all last:border-b-0">
            <div class="p-4 flex justify-between items-center">
                <div class="flex-1">
                    <div class="font-semibold text-gray-900">
                        {{ $conversation->citizen->name ?? $conversation->citizen->email }}
                    </div>
                    <div class="text-sm text-gray-500 mt-0.5">
                        Request #{{ $conversation->serviceRequest->reference_number }}
                    </div>
                    <div class="text-sm text-gray-400 truncate mt-1">
                        {{ $conversation->lastMessage->message ?? 'No messages yet' }}
                    </div>
                </div>
                <div class="text-right flex-shrink-0 ml-4">
                    <div class="text-xs text-gray-400">
                        {{ $conversation->last_message_at ? $conversation->last_message_at->diffForHumans() : 'No messages' }}
                    </div>
                    @if($conversation->unread_count > 0)
                        <span class="inline-block mt-2 px-2 py-1 text-xs font-bold text-white bg-red-500 rounded-full">
                            {{ $conversation->unread_count }}
                        </span>
                    @endif
                </div>
            </div>
        </a>
        @empty
            <div class="p-8 text-center text-gray-400">
                <i class="ti ti-message-2 text-4xl mb-3 block"></i>
                <p>No conversations yet.</p>
                <p class="text-sm mt-1">When citizens message you about their requests, they'll appear here.</p>
            </div>
        @endforelse
    </x-card>

    <div class="mt-4">
        {{ $conversations->links() }}
    </div>
</div>
@endsection