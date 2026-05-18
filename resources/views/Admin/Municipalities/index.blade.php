@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">
        Municipalities
    </h1>

    <a href="{{ route('admin.municipalities.create') }}">
        <x-button>
            + Add Municipality
        </x-button>
    </a>
</div>

<x-card>
    <x-table>
        {{-- HEAD --}}
        <x-slot name="head">
            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">ID</th>
            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Name</th>
            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Code</th>
            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Offices</th>
            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Status</th>
            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Actions</th>
        </x-slot>

        {{-- BODY --}}
        <x-slot name="body">
            @forelse($municipalities as $municipality)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-4 text-sm text-gray-700">
                        {{ $municipality->id }}
                    </td>
                    <td class="px-4 py-4 text-sm font-medium text-gray-900">
                        {{ $municipality->name }}
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-700">
                        <code class="bg-gray-100 px-2 py-1 rounded">{{ $municipality->code }}</code>
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-700">
                        <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded-md text-xs font-medium">
                            {{ $municipality->offices_count ?? $municipality->offices->count() }} Offices
                        </span>
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-700">
                        <span style="{{ $municipality->is_active ? 'background-color:#dcfce7;color:#166534' : 'background-color:#fee2e2;color:#991b1b' }}; padding:4px 12px; border-radius:9999px; font-size:12px; font-weight:500;">
                            {{ $municipality->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-700">
                        <div class="flex gap-2">
                            <a href="{{ route('admin.municipalities.show', $municipality->id) }}">
                                <x-button>Details</x-button>
                            </a>
                            <a href="{{ route('admin.municipalities.edit', $municipality->id) }}">
                                <x-button color="secondary">Edit</x-button>
                            </a>
                            <form method="POST"
                                action="{{ route('admin.municipalities.destroy', $municipality->id) }}"
                                onsubmit="return confirm('Are you sure? This will delete the municipality and all its data.')"
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
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                        No municipalities found. Click "Add Municipality" to create one.
                    </td>
                </tr>
            @endforelse
        </x-slot>
    </x-table>
    
    <div class="mt-6">
        {{ $municipalities->links() }}
    </div>
</x-card>

@endsection