@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-bold mb-6">
    Edit User #{{ $user->id }}
</h1>

<x-card>

    <form method="POST" action="{{ route('admin.users.update', $user->id) }}">

        @csrf
        @method('PUT')

        <x-input label="Full Name" name="name" value="{{ $user->name }}" />
        <x-input label="Email Address" name="email" type="email" value="{{ $user->email }}" />
        <x-input label="Phone Number" name="phone" value="{{ $user->phone }}" />
        <x-input label="National ID" name="national_id" value="{{ $user->national_id }}" />

        {{-- Role --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
            <select name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="staff" {{ $user->role === 'staff' ? 'selected' : '' }}>Staff</option>
                <option value="citizen" {{ $user->role === 'citizen' ? 'selected' : '' }}>Citizen</option>
            </select>
        </div>

        {{-- Active Status --}}
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select name="is_active" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="1" {{ $user->is_active ? 'selected' : '' }}>Active</option>
                <option value="0" {{ !$user->is_active ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between mt-6">
            <a 
                href="{{ route('admin.users.index') }}" 
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