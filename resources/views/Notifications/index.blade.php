@extends('layouts.app')

@section('content')

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
        <p class="text-sm text-gray-500 mt-1">All your recent notifications</p>
    </div>

    <form method="POST" action="{{ route('notifications.readAll') }}">
        @csrf
        <button type="submit"
                class="text-sm text-blue-600 hover:underline"
                style="background: none; border: none; cursor: pointer;">
            Mark all as read
        </button>
    </form>
</div>

<x-card>

    @if($notifications->isEmpty())

        <div class="py-16 text-center">
            <i class="ti ti-bell-off" style="font-size: 40px; color: #d1d5db; display: block; margin-bottom: 12px;"></i>
            <p class="text-gray-400">No notifications yet</p>
        </div>

    @else

        <div class="divide-y divide-gray-100">
            {{-- TODO: loop through real notifications here --}}
            {{--
            @foreach($notifications as $notification)
                <div class="flex items-start gap-4 py-4 {{ $notification->read_at ? '' : 'bg-blue-50' }}">

                    <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <i class="ti ti-clipboard-list text-blue-600" style="font-size: 16px;"></i>
                    </div>

                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ $notification->data['title'] }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $notification->data['body'] }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>

                    @if(!$notification->read_at)
                        <div class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0 mt-2"></div>
                    @endif

                </div>
            @endforeach
            --}}
        </div>

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>

    @endif

</x-card>

@endsection