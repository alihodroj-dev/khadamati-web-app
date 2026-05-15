@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-bold mb-6">
    Edit User #{{ $id }}
</h1>

<x-card>

    <form>

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

        <div class="mb-4">

            <label class="block text-sm font-medium text-gray-700 mb-2">
                Role
            </label>

            <select
                name="role"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
                <option selected>Admin</option>
                <option>Staff</option>
            </select>

        </div>

        <x-button type="submit">
            Update User
        </x-button>

    </form>

</x-card>

@endsection