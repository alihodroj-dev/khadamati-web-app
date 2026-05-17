@extends('layouts.app')

@section('content')

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
        <p class="text-sm text-gray-500 mt-1">
            @if($unreadCount > 0)
                {{ $unreadCount }} unread
            @else
                All caught up
            @endif
        </p>
    </div>

    @if($unreadCount > 0)
        <form method="POST" action="{{ route('notifications.readAll') }}">
            @csrf
            <button type="submit"
                    class="text-sm text-blue-600 hover:underline bg-transparent border-0 cursor-pointer">
                Mark all as read
            </button>
        </form>
    @endif
</div>

<x-card>
    @if($notifications->isEmpty())
        <div class="py-16 text-center">
            <i class="ti ti-bell-off" style="font-size: 40px; color: #d1d5db; display: block; margin-bottom: 12px;"></i>
            <p class="text-gray-400">No notifications yet</p>
        </div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($notifications as $notification)
                <x-notification-item :notification="$notification" />
            @endforeach
        </div>

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</x-card>

@endsection
