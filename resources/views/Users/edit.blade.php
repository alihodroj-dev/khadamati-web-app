@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-bold mb-6">
    Edit User #{{ $id }}
</h1>

<x-card>

    <form method="POST" action="{{ route('users.update', $id) }}">

        @csrf
        @method('PUT')

        <x-input
            label="Full Name"
            name="name"
            value="John Doe"
        />

        <x-input
            label="Email Address"
            name="email"
            type="email"
            value="john@example.com"
        />

        <x-input
            label="Phone Number"
            name="phone"
            value="+96170000000"
        />

        <x-input
            label="National ID"
            name="national_id"
            value="123456789"
        />

        {{-- Role --}}
        <div class="mb-4">

            <label class="block text-sm font-medium text-gray-700 mb-2">
                Role
            </label>

            <select
                name="role"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
                <option value="admin" selected>
                    Admin
                </option>

                <option value="staff">
                    Staff
                </option>

                <option value="citizen">
                    Citizen
                </option>

            </select>

        </div>

        {{-- Active Status --}}
        <div class="mb-6">

            <label class="block text-sm font-medium text-gray-700 mb-2">
                Status
            </label>

            <select
                name="is_active"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
                <option value="1" selected>
                    Active
                </option>

                <option value="0">
                    Inactive
                </option>

            </select>

        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between mt-6">
            <a 
                href="{{ route('users.index') }}" 
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out"
            >
                Back to Users
            </a>

            <x-button type="submit">
                Update User
            </x-button>
        </div>

    </form>

</x-card>

@endsection