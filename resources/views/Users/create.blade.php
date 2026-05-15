@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-bold mb-6">
    Create User
</h1>

<x-card>

    <form>

        <x-input
            label="Full Name"
            name="name"
        />

        <x-input
            label="Email Address"
            name="email"
            type="email"
        />

        <x-input
            label="Password"
            name="password"
            type="password"
        />

        <div class="mb-4">

            <label class="block text-sm font-medium text-gray-700 mb-2">
                Role
            </label>

            <select
                name="role"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
                <option>Admin</option>
                <option>Staff</option>
                <option>Citizen</option>
            </select>

        </div>

        <x-button type="submit">
            Save User
        </x-button>

    </form>

</x-card>

@endsection