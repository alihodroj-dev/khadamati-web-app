@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">
        Users
    </h1>

    <a href="{{ route('admin.users.create') }}">
        <x-button>
            + Add User
        </x-button>
    </a>
</div>

<x-card>
    <x-table>
        {{-- HEAD --}}
        <x-slot name="head">
            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">ID</th>
            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Name</th>
            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Role</th>
            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Actions</th>
        </x-slot>

        {{-- BODY --}}
        <x-slot name="body">
            @forelse($users as $user)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-4 text-sm text-gray-700">
                        {{ $user->id }}
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-700">
                        {{ $user->name }}
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-700">
                        <span style="
                            @switch($user->role)
                                @case('admin')
                                    background-color:#dcfce7; color:#166534;
                                    @break
                                @case('staff')
                                    background-color:#dbeafe; color:#1e40af;
                                    @break
                                @case('citizen')
                                    background-color:#fee2e2; color:#991b1b;
                                    @break
                                @default
                                    background-color:#f3f4f6; color:#374151;
                            @endswitch
                            padding:4px 12px; border-radius:9999px; font-size:12px; font-weight:500;
                        ">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>

                    <td class="px-4 py-4 text-sm text-gray-700">
                        <div class="flex gap-2 justify-center">
                            <a href="{{ route('admin.users.show', $user->id) }}">
                                <x-button>Details</x-button>
                            </a>
                            <a href="{{ route('admin.users.edit', $user->id) }}">
                                <x-button color="secondary">Edit</x-button>
                            </a>
                            <form method="POST"
                                action="{{ route('admin.users.destroy', $user->id) }}"
                                onsubmit="return confirm('Are you sure?')"
                                class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background-color:#ef4444;color:white;padding:8px 16px;border-radius:8px;border:none;cursor:pointer;">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-4 text-sm text-gray-700">No Users found</td>
                </tr>
            @endforelse
        </x-slot>
    </x-table>
</x-card>

@endsection