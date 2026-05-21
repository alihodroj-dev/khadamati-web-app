@props([
    'notification',
    'compact' => false,
])

@php
    $isUnread = $notification['is_unread'] ?? false;
    $url = $notification['url'] ?? null;
@endphp

<div class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 {{ $isUnread ? 'bg-blue-50/60' : '' }}">

    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 {{ $compact ? 'mt-0.5' : '' }}">
        <i class="{{ $notification['icon'] }} text-blue-600" style="font-size: {{ $compact ? '15px' : '16px' }};"></i>
    </div>

    <div class="flex-1 min-w-0">
        @if($url)
            <a href="{{ $url }}" class="block group">
                <p class="text-sm text-gray-900 font-medium group-hover:text-blue-700">{{ $notification['title'] }}</p>
                @if($notification['body'])
                    <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $notification['body'] }}</p>
                @endif
            </a>
        @else
            <p class="text-sm text-gray-900 font-medium">{{ $notification['title'] }}</p>
            @if($notification['body'])
                <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $notification['body'] }}</p>
            @endif
        @endif

        <p class="text-xs text-gray-400 mt-1">{{ $notification['created_at_human'] }}</p>
    </div>

    <div class="flex flex-col items-end gap-2 flex-shrink-0">
        @if($isUnread)
            <span class="w-2 h-2 rounded-full bg-blue-500" title="Unread"></span>
        @endif

        @if($isUnread)
            <form method="POST" action="{{ route('notifications.read', $notification['id']) }}">
                @csrf
                <button type="submit"
                        class="text-xs text-blue-600 hover:underline"
                        style="background: none; border: none; cursor: pointer; padding: 0;">
                    Mark read
                </button>
            </form>
        @endif
    </div>

</div>
