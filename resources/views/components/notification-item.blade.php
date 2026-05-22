@props(['notification', 'compact' => false])

@php
    $isArray = is_array($notification);
    $title   = $isArray ? ($notification['title'] ?? 'Notification') : ($notification->data['title'] ?? 'Notification');
    $body    = $isArray ? ($notification['body'] ?? '')              : ($notification->data['body'] ?? '');
    $isRead  = $isArray ? ! ($notification['is_unread'] ?? true)     : ! is_null($notification->read_at);
    $time    = $isArray ? ($notification['created_at_human'] ?? '')  : optional($notification->created_at)->diffForHumans();
@endphp

@if($compact)
    <a href="{{ route('notifications.index') }}"
       class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition {{ $isRead ? '' : 'bg-blue-50' }}">
        <span class="mt-1 flex-shrink-0 w-2 h-2 rounded-full {{ $isRead ? 'bg-transparent' : 'bg-blue-500' }}"></span>
        <div class="min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">{{ $title }}</p>
            <p class="text-xs text-gray-500 mt-0.5 line-clamp-1">{{ $body }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $time }}</p>
        </div>
    </a>
@else
    <div class="flex items-start gap-4 px-4 py-4 {{ $isRead ? '' : 'bg-blue-50' }}">
        <span class="mt-2 flex-shrink-0 w-2.5 h-2.5 rounded-full {{ $isRead ? 'bg-gray-300' : 'bg-blue-500' }}"></span>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-900">{{ $title }}</p>
            <p class="text-sm text-gray-600 mt-0.5">{{ $body }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $time }}</p>
        </div>
    </div>
@endif
