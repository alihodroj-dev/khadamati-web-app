@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
            <p class="text-gray-500 text-sm mt-1">Stay updated with your requests and payments</p>
        </div>
        
        @if($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.readAll') }}">
                @csrf
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Mark all as read
                </button>
            </form>
        @endif
    </div>

    @if($notifications->count() > 0)
        <div class="space-y-3">
            @foreach($notifications as $notification)
                <div class="bg-white rounded-xl p-5 shadow-sm hover:shadow-md transition {{ isset($notification['is_unread']) && $notification['is_unread'] ? 'border-l-4 border-l-blue-600' : '' }}" style="border: 0.5px solid #e5e7eb;">
                    
                    <div class="flex items-start gap-4">
                        <!-- Icon -->
                        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0
                            @if(str_contains($notification['type'] ?? '', 'Request')) bg-blue-100
                            @elseif(str_contains($notification['type'] ?? '', 'Payment')) bg-green-100
                            @elseif(str_contains($notification['type'] ?? '', 'Appointment')) bg-purple-100
                            @else bg-gray-100
                            @endif">
                            <i class="ti 
                                @if(str_contains($notification['type'] ?? '', 'Request')) ti-clipboard-list text-blue-600
                                @elseif(str_contains($notification['type'] ?? '', 'Payment')) ti-credit-card text-green-600
                                @elseif(str_contains($notification['type'] ?? '', 'Appointment')) ti-calendar text-purple-600
                                @else ti-bell text-gray-600
                                @endif text-xl">
                            </i>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $notification['title'] ?? 'Notification' }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">{{ $notification['body'] ?? '' }}</p>
                                </div>
                                <span class="text-xs text-gray-400">{{ $notification['created_at_human'] ?? '' }}</span>
                            </div>
                            
                            <!-- Actions -->
                            <div class="mt-3 flex gap-3">
                                @if(isset($notification['is_unread']) && $notification['is_unread'])
                                    <form method="POST" action="{{ route('notifications.read', $notification['id']) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs text-blue-600 hover:underline">
                                            Mark as read
                                        </button>
                                    </form>
                                @endif
                                
                                @if($notification['url'] ?? false)
                                    <a href="{{ $notification['url'] }}" class="text-xs text-gray-500 hover:text-gray-700">
                                        View details →
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                </div>
            @endforeach
        </div>
        
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
        
    @else
        <div class="bg-white rounded-xl p-12 text-center" style="border: 0.5px solid #e5e7eb;">
            <i class="ti ti-bell-off text-gray-300 text-5xl mb-3 block"></i>
            <p class="text-gray-500">No notifications yet</p>
            <p class="text-sm text-gray-400 mt-1">You'll see updates about your requests here</p>
        </div>
    @endif

</div>
@endsection