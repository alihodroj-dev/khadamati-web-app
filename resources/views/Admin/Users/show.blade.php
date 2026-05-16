@extends('layouts.app')

@section('content')

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
            <a href="{{ route('admin.users.index') }}" class="hover:text-blue-600 transition">Users</a>
            <span>/</span>
            <span>Details</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">
            User Details <span class="text-gray-400 font-mono text-lg ml-2">#{{ $id }}</span>
        </h1>
    </div>

    <div class="flex gap-3">
        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
            Back
        </a>
        <a href="{{ route('admin.users.edit', $id) }}">
            <x-button color="secondary">
                Edit User
            </x-button>
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

    {{-- MAIN INFO --}}
    <div class="lg:col-span-2 space-y-8">

        <x-card class="h-fit">

            <h2 class="text-lg font-semibold mb-6">
                Personal Information
            </h2>

            <div class="space-y-6 leading-relaxed">

                <div>
                    <p class="text-sm text-gray-500">Name</p>
                    <p class="text-base font-medium">John Doe</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Email</p>
                    <p class="text-base font-medium">john@example.com</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Phone</p>
                    <p class="text-base font-medium">+96170000000</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">National ID</p>
                    <p class="text-base font-medium">123456789</p>
                </div>

            </div>

        </x-card>

    </div>

    {{-- SIDE INFO --}}
    <div class="space-y-8">

        <x-card class="h-fit">

            <h2 class="text-lg font-semibold mb-6">
                Account Info
            </h2>

            <div class="space-y-5">

                <div class="flex justify-between items-center">

                    <span class="text-gray-600">Role</span>

                    <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-xs">
                        Admin
                    </span>

                </div>

                <div class="flex justify-between items-center">

                    <span class="text-gray-600">Status</span>

                    <span class="px-3 py-1 rounded-full bg-green-100 text-green-800 text-xs">
                        Active
                    </span>

                </div>

            </div>

        </x-card>

    </div>

</div>

@endsection