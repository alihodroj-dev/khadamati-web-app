@php
    $role = auth()->user()->role;
    $userName = auth()->user()->name;
    $initials = collect(explode(' ', $userName))->map(fn($w) => strtoupper($w[0]))->take(2)->join('');
@endphp

<aside class="w-64 min-h-screen flex flex-col" style="background: #1e3a5f;">

    {{-- BRAND --}}
    <div class="px-4 py-5" style="border-bottom: 0.5px solid rgba(255,255,255,0.1);">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-500 flex items-center justify-center text-white font-medium text-lg">
                K
            </div>
            <div>
                <p class="text-white font-medium text-base leading-tight">Khadamati</p>
                <p class="text-xs" style="color: rgba(255,255,255,0.45);">Government Portal</p>
            </div>
        </div>
    </div>

    {{-- NAV --}}
    <nav class="flex-1 px-3 py-4 overflow-y-auto">

        @if($role === 'admin')

            {{-- MAIN --}}
            <div class="mb-6">
                <p class="px-2 mb-2 text-xs font-medium uppercase tracking-wider" style="color: rgba(255,255,255,0.4);">Main</p>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg mb-1 text-sm transition-all
                    {{ request()->routeIs('admin.dashboard') ? 'bg-white font-medium' : 'hover:bg-white/10' }}"
                    style="{{ request()->routeIs('admin.dashboard') ? 'color: #1e3a5f;' : 'color: rgba(255,255,255,0.75);' }}">
                    <i class="ti ti-layout-dashboard text-lg" aria-hidden="true"></i>
                    Dashboard
                </a>
            </div>

            {{-- MANAGEMENT --}}
            <div class="mb-6">
                <p class="px-2 mb-2 text-xs font-medium uppercase tracking-wider" style="color: rgba(255,255,255,0.4);">Management</p>

                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg mb-1 text-sm transition-all
                    {{ request()->routeIs('admin.users.*') ? 'bg-white font-medium' : 'hover:bg-white/10' }}"
                    style="{{ request()->routeIs('admin.users.*') ? 'color: #1e3a5f;' : 'color: rgba(255,255,255,0.75);' }}">
                    <i class="ti ti-users text-lg" aria-hidden="true"></i>
                    Users
                </a>

                <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg mb-1 text-sm transition-all
                    {{ request()->routeIs('admin.categories.*') ? 'bg-white font-medium' : 'hover:bg-white/10' }}"
                    style="{{ request()->routeIs('admin.categories.*') ? 'color: #1e3a5f;' : 'color: rgba(255,255,255,0.75);' }}">
                    <i class="ti ti-category text-lg" aria-hidden="true"></i>
                    Categories
                </a>

                <a href="{{ route('admin.services.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg mb-1 text-sm transition-all
                    {{ request()->routeIs('admin.services.*') ? 'bg-white font-medium' : 'hover:bg-white/10' }}"
                    style="{{ request()->routeIs('admin.services.*') ? 'color: #1e3a5f;' : 'color: rgba(255,255,255,0.75);' }}">
                    <i class="ti ti-briefcase text-lg" aria-hidden="true"></i>
                    Services
                </a>

                <a href="{{ route('admin.offices.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg mb-1 text-sm transition-all
                    {{ request()->routeIs('admin.offices.*') ? 'bg-white font-medium' : 'hover:bg-white/10' }}"
                    style="{{ request()->routeIs('admin.offices.*') ? 'color: #1e3a5f;' : 'color: rgba(255,255,255,0.75);' }}">
                    <i class="ti ti-building-community text-lg" aria-hidden="true"></i>
                    Offices
                </a>
            </div>

            {{-- OPERATIONS --}}
            <div class="mb-6">
                <p class="px-2 mb-2 text-xs font-medium uppercase tracking-wider" style="color: rgba(255,255,255,0.4);">Operations</p>

                <a href="{{ route('admin.requests.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg mb-1 text-sm transition-all
                    {{ request()->routeIs('admin.requests.*') ? 'bg-white font-medium' : 'hover:bg-white/10' }}"
                    style="{{ request()->routeIs('admin.requests.*') ? 'color: #1e3a5f;' : 'color: rgba(255,255,255,0.75);' }}">
                    <i class="ti ti-clipboard-list text-lg" aria-hidden="true"></i>
                    Requests
                </a>

                <a href="{{ route('admin.appointments.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg mb-1 text-sm transition-all
                    {{ request()->routeIs('admin.appointments.*') ? 'bg-white font-medium' : 'hover:bg-white/10' }}"
                    style="{{ request()->routeIs('admin.appointments.*') ? 'color: #1e3a5f;' : 'color: rgba(255,255,255,0.75);' }}">
                    <i class="ti ti-calendar text-lg" aria-hidden="true"></i>
                    Appointments
                </a>

                <a href="{{ route('admin.payments.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg mb-1 text-sm transition-all
                    {{ request()->routeIs('admin.payments.*') ? 'bg-white font-medium' : 'hover:bg-white/10' }}"
                    style="{{ request()->routeIs('admin.payments.*') ? 'color: #1e3a5f;' : 'color: rgba(255,255,255,0.75);' }}">
                    <i class="ti ti-credit-card text-lg" aria-hidden="true"></i>
                    Payments
                </a>
            </div>

            {{-- ANALYTICS --}}
            <div>
                <p class="px-2 mb-2 text-xs font-medium uppercase tracking-wider" style="color: rgba(255,255,255,0.4);">Analytics</p>

                <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg mb-1 text-sm transition-all
                    {{ request()->routeIs('admin.reports.*') ? 'bg-white font-medium' : 'hover:bg-white/10' }}"
                    style="{{ request()->routeIs('admin.reports.*') ? 'color: #1e3a5f;' : 'color: rgba(255,255,255,0.75);' }}">
                    <i class="ti ti-chart-bar text-lg" aria-hidden="true"></i>
                    Reports
                </a>
            </div>

        @endif

        @if($role === 'staff')

            <div class="mb-6">
                <p class="px-2 mb-2 text-xs font-medium uppercase tracking-wider" style="color: rgba(255,255,255,0.4);">Staff Panel</p>

                <a href="{{ route('staff.dashboard') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg mb-1 text-sm transition-all
                    {{ request()->routeIs('staff.dashboard') ? 'bg-white font-medium' : 'hover:bg-white/10' }}"
                    style="{{ request()->routeIs('staff.dashboard') ? 'color: #1e3a5f;' : 'color: rgba(255,255,255,0.75);' }}">
                    <i class="ti ti-layout-dashboard text-lg" aria-hidden="true"></i>
                    Dashboard
                </a>

                <a href="{{ route('staff.requests.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg mb-1 text-sm transition-all
                    {{ request()->routeIs('staff.requests.*') ? 'bg-white font-medium' : 'hover:bg-white/10' }}"
                    style="{{ request()->routeIs('staff.requests.*') ? 'color: #1e3a5f;' : 'color: rgba(255,255,255,0.75);' }}">
                    <i class="ti ti-clipboard-list text-lg" aria-hidden="true"></i>
                    My Requests
                </a>

                <a href="{{ route('staff.appointments.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg mb-1 text-sm transition-all
                    {{ request()->routeIs('staff.appointments.*') ? 'bg-white font-medium' : 'hover:bg-white/10' }}"
                    style="{{ request()->routeIs('staff.appointments.*') ? 'color: #1e3a5f;' : 'color: rgba(255,255,255,0.75);' }}">
                    <i class="ti ti-calendar text-lg" aria-hidden="true"></i>
                    My Appointments
                </a>
            </div>

        @endif

    </nav>

    {{-- FOOTER --}}
    <div class="p-3" style="border-top: 0.5px solid rgba(255,255,255,0.1);">
        <div class="flex items-center gap-3 px-3 py-3 rounded-xl" 
            style="background: rgba(255,255,255,0.08); border: 0.5px solid rgba(255,255,255,0.1);">
            <div class="w-9 h-9 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-medium flex-shrink-0">
                {{ $initials }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-white text-sm font-medium truncate leading-snug">{{ $userName }}</p>
                <p class="text-xs capitalize leading-snug" style="color: rgba(255,255,255,0.4);">{{ $role }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                        class="w-8 h-8 rounded-lg flex items-center justify-center transition-all hover:bg-white/10"
                        style="color: rgba(255,255,255,0.4); background: none; border: none; cursor: pointer;">
                    <i class="ti ti-logout text-lg" aria-hidden="true"></i>
                </button>
            </form>
        </div>
    </div>

</aside>