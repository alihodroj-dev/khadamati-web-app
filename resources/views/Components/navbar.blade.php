@php
    $userName = auth()->user()->name;
    $role = auth()->user()->role;
    $initials = collect(explode(' ', $userName))->map(fn($w) => strtoupper($w[0]))->take(2)->join('');

    $segments = collect(request()->segments())->skip(1);
    $breadcrumb = $segments->map(fn($s) => ucfirst(str_replace('-', ' ', $s)));

    // Get notifications for dropdown
    $navNotifications = auth()->user()->notifications()->latest()->take(5)->get();
    $navUnreadCount = auth()->user()->unreadNotifications->count();
@endphp

<header class="bg-white flex items-center justify-between px-6"
        style="height: 62px; border-bottom: 0.5px solid var(--color-border-tertiary, #e5e7eb);">

    <div class="flex items-center gap-2 text-sm">
        <span class="text-gray-400 capitalize">{{ $role }}</span>
        @foreach($breadcrumb as $crumb)
            <span class="text-gray-300">/</span>
            <span class="{{ $loop->last ? 'text-gray-900 font-medium' : 'text-gray-400' }}">
                {{ $crumb }}
            </span>
        @endforeach
    </div>

    <div class="flex items-center gap-4">

        {{-- NOTIFICATIONS DROPDOWN --}}
        <div class="relative" x-data="{ open: false }">
            <button type="button"
                    @click="open = !open"
                    class="relative flex items-center justify-center w-10 h-10 rounded-lg transition hover:bg-gray-50"
                    style="border: 1px solid #e5e7eb; background: white; cursor: pointer;"
                    aria-label="Notifications">
                
                {{-- Bell icon changes color based on unread count --}}
                <i class="ti ti-bell" style="font-size: 20px; color: {{ $navUnreadCount > 0 ? '#ef4444' : '#6b7280' }};"></i>
                
                {{-- Red badge with count --}}
                @if($navUnreadCount > 0)
                    <span class="absolute -top-1 -right-1 min-w-[20px] h-[20px] px-1 rounded-full bg-red-500 text-white text-[11px] font-bold flex items-center justify-center shadow-md"
                        style="box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                        {{ $navUnreadCount > 99 ? '99+' : $navUnreadCount }}
                    </span>
                @endif
            </button>

            <!-- Dropdown content (same as before) -->
            <div x-show="open"
                @click.outside="open = false"
                x-transition
                class="absolute right-0 mt-2 bg-white rounded-xl shadow-lg z-50"
                style="width: 320px; border: 0.5px solid #e5e7eb; top: 100%; display: none;">

                <div class="flex items-center justify-between px-4 py-3"
                    style="border-bottom: 0.5px solid #e5e7eb;">
                    <p class="text-sm font-medium text-gray-900">Notifications</p>

                    @if($navUnreadCount > 0)
                        <form method="POST" action="{{ route('notifications.readAll') }}">
                            @csrf
                            <button type="submit"
                                    class="text-xs text-blue-600 hover:underline"
                                    style="background: none; border: none; cursor: pointer;">
                                Mark all as read
                            </button>
                        </form>
                    @endif
                </div>

                <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                    @forelse($navNotifications as $notification)
                        <div class="px-4 py-3 hover:bg-gray-50 transition {{ $notification->read_at ? '' : 'bg-blue-50' }}">
                            <div class="flex gap-2">
                                <i class="ti ti-info-circle text-blue-500 text-sm mt-0.5"></i>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium {{ $notification->read_at ? 'text-gray-600' : 'text-gray-900' }}">
                                        {{ $notification->data['title'] ?? 'Notification' }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $notification->data['body'] ?? '' }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                </div>
                                @if(!$notification->read_at)
                                    <div class="w-2 h-2 rounded-full bg-blue-500 mt-1"></div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center">
                            <i class="ti ti-bell-off" style="font-size: 32px; color: #d1d5db; display: block; margin-bottom: 8px;"></i>
                            <p class="text-sm text-gray-400">No notifications yet</p>
                        </div>
                    @endforelse
                </div>

                <div class="px-4 py-3" style="border-top: 0.5px solid #e5e7eb;">
                    <a href="{{ route('notifications.index') }}"
                    class="text-xs text-blue-600 hover:underline">
                        View all notifications
                    </a>
                </div>

            </div>
        </div>

        <div class="w-px h-7 bg-gray-200"></div>

        <div class="flex items-center gap-2.5 cursor-pointer hover:bg-gray-50 transition"
            style="padding: 6px 12px 6px 6px; border-radius: 10px; border: 1px solid #e5e7eb; background: white;">

            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-medium flex-shrink-0"
                style="background: #1e3a5f;">
                {{ $initials }}
            </div>

            <div>
                <p class="text-sm font-medium text-gray-900 leading-snug">{{ $userName }}</p>
                <p class="text-xs text-gray-400 capitalize leading-snug">{{ $role }}</p>
            </div>

            <form method="POST" action="{{ route('logout') }}" style="margin-left: 8px;">
                @csrf
                <button type="submit"
                        style="background: none; border: none; cursor: pointer; color: #9ca3af; display: flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 6px; padding: 0;"
                        onmouseover="this.style.background='#f3f4f6'; this.style.color='#374151'"
                        onmouseout="this.style.background='none'; this.style.color='#9ca3af'"
                        title="Logout">
                    <i class="ti ti-logout" style="font-size: 16px;" aria-hidden="true"></i>
                </button>
            </form>

        </div>

    </div>

</header>