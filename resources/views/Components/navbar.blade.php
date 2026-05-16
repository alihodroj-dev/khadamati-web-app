@php
    $userName = auth()->user()->name;
    $role = auth()->user()->role;
    $initials = collect(explode(' ', $userName))->map(fn($w) => strtoupper($w[0]))->take(2)->join('');

    $segments = collect(request()->segments())->skip(1);
    $breadcrumb = $segments->map(fn($s) => ucfirst(str_replace('-', ' ', $s)));
@endphp

<header class="bg-white flex items-center justify-between px-6"
        style="height: 62px; border-bottom: 0.5px solid var(--color-border-tertiary, #e5e7eb);">

    {{-- LEFT: Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm">
        <span class="text-gray-400 capitalize">{{ $role }}</span>
        @foreach($breadcrumb as $crumb)
            <span class="text-gray-300">/</span>
            <span class="{{ $loop->last ? 'text-gray-900 font-medium' : 'text-gray-400' }}">
                {{ $crumb }}
            </span>
        @endforeach
    </div>

    {{-- RIGHT --}}
    <div class="flex items-center gap-4">

        {{-- Notification bell --}}
        <div style="width: 36px; height: 36px; border-radius: 8px; border: 1px solid #e5e7eb; background: white; display: flex; align-items: center; justify-content: center; color: #6b7280; cursor: pointer; position: relative;">
            <i class="ti ti-bell" style="font-size: 18px;" aria-hidden="true"></i>
        </div>

        {{-- Divider --}}
        <div class="w-px h-7 bg-gray-200"></div>

        {{-- User pill --}}
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