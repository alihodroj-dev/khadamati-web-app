@php
    $role = auth()->user()->role;
@endphp

<aside class="w-64 bg-blue-900 text-white min-h-screen">

    <div class="p-6 text-2xl font-bold border-b border-blue-800">
        Khadamati
    </div>

    <nav class="mt-6">

        @if($role === 'admin')

            <a href="{{ route('admin.dashboard') }}"
               class="block px-6 py-3 hover:bg-blue-800">
                Dashboard
            </a>

            <a href="#" class="block px-6 py-3 hover:bg-blue-800">
                Users
            </a>

            <a href="#" class="block px-6 py-3 hover:bg-blue-800">
                Reports
            </a>

            <a href="{{ route('categories.index') }}"
            class="block px-6 py-3 hover:bg-blue-800 {{ request()->routeIs('categories.*') ? 'bg-blue-800' : '' }}">
                Categories
            </a>

            <a href="{{ route('services.index') }}"
            class="block px-6 py-3 hover:bg-blue-800 {{ request()->routeIs('services.*') ? 'bg-blue-800' : '' }}">
                Services
            </a>

        @endif

        @if($role === 'staff')

            <a href="{{ route('staff.dashboard') }}"
               class="block px-6 py-3 hover:bg-blue-800">
                Dashboard
            </a>

            <a href="#" class="block px-6 py-3 hover:bg-blue-800">
                Requests
            </a>

        @endif

    </nav>

</aside>