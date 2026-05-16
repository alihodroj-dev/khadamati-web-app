@php
    $role = auth()->user()->role;
@endphp

<aside class="w-72 min-h-screen bg-blue-900 border-r border-gray-200 flex flex-col">

    {{-- LOGO / BRAND --}}
    <div class="px-6 py-6 border-b border-gray-100">

        <div class="flex items-center gap-3">

            <div class="w-11 h-11 rounded-xl bg-blue-600 flex items-center justify-center text-white font-bold text-lg shadow-sm">
                K
            </div>

            <div>

                <h1 class="text-xl font-bold text-white leading-tight">
                    Khadamati
                </h1>

                <p class="text-sm text-blue-200">
                    Government Portal
                </p>

            </div>

        </div>

    </div>

    @if($role === 'admin')

    {{-- NAVIGATION --}}
    <nav class="flex-1 px-4 py-6 overflow-y-auto">

        {{-- MAIN --}}
        <div class="mb-8">

            <p class="px-3 mb-3 text-xs font-semibold tracking-wider text-blue-200 uppercase">
                Main
            </p>

            <div class="space-y-1">

                <a href="{{ route('admin.dashboard') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200
                {{ request()->routeIs('admin.dashboard')
                        ? 'bg-white text-blue-700 font-semibold shadow-sm'
                        : 'text-gray-600 hover:bg-blue-800 hover:text-gray-900'
                }}">
                    <span>Dashboard</span>
                </a>


            </div>

        </div>

        {{-- MANAGEMENT --}}
        <div class="mb-8">

            <p class="px-3 mb-3 text-xs font-semibold tracking-wider text-blue-200 uppercase">
                Management
            </p>

            <div class="space-y-1">

                {{-- USERS --}}
                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200

                   {{ request()->routeIs('admin.users.*')
                        ? 'bg-white text-blue-700 font-semibold shadow-sm'
                        : 'text-white hover:bg-blue-800 hover:text-black-900'
                   }}">

                    <span>
                        Users
                    </span>

                </a>

                {{-- CATEGORIES --}}
                <a href="{{ route('admin.categories.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200

                   {{ request()->routeIs('admin.categories.*')
                        ? 'bg-white text-blue-700 font-semibold shadow-sm'
                        : 'text-white hover:bg-blue-800 hover:text-gray-900'
                   }}">

                    <span>
                        Categories
                    </span>

                </a>

                {{-- SERVICES --}}
                <a href="{{ route('admin.services.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200

                   {{ request()->routeIs('admin.services.*')
                        ? 'bg-white text-blue-700 font-semibold shadow-sm'
                        : 'text-white hover:bg-blue-800 hover:text-black'
                   }}">

                    <span>
                        Services
                    </span>

                </a>

            </div>

        </div>

        {{-- OPERATIONS --}}
        <div class="mb-8">

            <p class="px-3 mb-3 text-xs font-semibold tracking-wider text-blue-200 uppercase">
                Operations
            </p>

            <div class="space-y-1">

                {{-- Requests --}}
                <a href="{{ route('admin.requests.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200

                   {{ request()->routeIs('admin.requests.*')
                        ? 'bg-white text-blue-700 font-semibold shadow-sm'
                        : 'text-white hover:bg-blue-800 hover:text-black'
                   }}">

                    <span>
                        Requests
                    </span>

                </a>

                <a href="{{ route('admin.appointments.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200

                   {{ request()->routeIs('admin.appointments.*')
                        ? 'bg-white text-blue-700 font-semibold shadow-sm'
                        : 'text-white hover:bg-blue-800 hover:text-black'
                   }}">

                    <span>
                        Appointments
                    </span>

                </a>

                <a href="{{ route('admin.payments.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200

                   {{ request()->routeIs('admin.payments.*')
                        ? 'bg-white text-blue-700 font-semibold shadow-sm'
                        : 'text-white hover:bg-blue-800 hover:text-black'
                   }}">

                    <span>
                        Payments
                    </span>

                </a>

            </div>

        </div>

        {{-- ANALYTICS --}}
        <div>

            <p class="px-3 mb-3 text-xs font-semibold tracking-wider text-blue-200 uppercase">
                Analytics
            </p>

            <div class="space-y-1">

                <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-blue-200 bg-blue-800">

                    <span>
                        Reports
                    </span>

                </div>

            </div>

        </div>

    </nav>

    @endif

    @if($role === 'staff')

        {{-- STAFF NAVIGATION --}}
        <div class="px-6 py-4">

            <div class="mb-6">

                <p class="text-xs text-blue-200 uppercase mb-3">
                    Staff Panel
                </p>

                <a href="{{ route('staff.dashboard') }}"
                class="flex items-center px-4 py-3 rounded-xl text-white hover:bg-blue-800">

                    Dashboard

                </a>

                <a href="{{ route('staff.requests.index') }}"
                class="flex items-center px-4 py-3 rounded-xl text-white hover:bg-blue-800">

                    My Requests

                </a>

                <a href="{{ route('staff.appointments.index') }}"
                class="flex items-center px-4 py-3 rounded-xl text-white hover:bg-blue-800">

                    My Appointments

                </a>

            </div>

        </div>

    @endif

    {{-- FOOTER --}}
    <div class="border-t border-gray-100 p-4">

        <div class="flex items-center gap-3 px-3 py-3 rounded-xl bg-blue-800">

            <img
                src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}"
                class="w-10 h-10 rounded-full"
            >

            <div class="min-w-0">

                <p class="text-sm font-semibold text-gray-900 truncate">
                    {{ auth()->user()->name }}
                </p>

                <p class="text-xs text-gray-500 capitalize">
                    {{ auth()->user()->role }}
                </p>

            </div>

        </div>

    </div>

</aside>