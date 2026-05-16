@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">
        Services
    </h1>

    <a href="{{ route('admin.services.create') }}">
        <x-button>
            + Add Service
        </x-button>
    </a>
</div>

<x-card>
    <x-table>
        {{-- HEAD --}}
        <x-slot name="head">
            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">ID</th>
            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Name</th>
            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Status</th>
            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Actions</th>
        </x-slot>

        {{-- BODY --}}
        <x-slot name="body">
            @forelse($services as $service)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-4 text-sm text-gray-700">
                        {{ $service->id }}
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-700">
                        {{ $service->name }}
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-700">
                        <span style="{{ $service->is_active ? 'background-color:#dcfce7;color:#166534' : 'background-color:#fee2e2;color:#991b1b' }}; padding:4px 12px; border-radius:9999px; font-size:12px; font-weight:500;">
                            {{ $service->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-700">
                        <div class="flex gap-2 justify-center">
                            <a href="{{ route('admin.services.show', $service->id) }}">
                                <x-button>Details</x-button>
                            </a>
                            <a href="{{ route('admin.services.edit', $service->id) }}">
                                <x-button color="secondary">Edit</x-button>
                            </a>
                            <form method="POST"
                                action="{{ route('admin.services.destroy', $service->id) }}"
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
                    <td class="px-4 py-4 text-sm text-gray-700">No categories found</td>
                </tr>
            @endforelse
        </x-slot>
    </x-table>
</x-card>

@endsection