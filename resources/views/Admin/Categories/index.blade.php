@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">
        Categories
    </h1>

    <a href="{{ route('admin.categories.create') }}">
        <x-button>
            + Add Category
        </x-button>
    </a>
</div>

<x-card>
    <x-table>
        {{-- HEAD --}}
        <x-slot name="head">
            <th class="px-4 py-3 text-left border border-gray-200">ID</th>
            <th class="px-4 py-3 text-left border border-gray-200">Name</th>
            <th class="px-4 py-3 text-left border border-gray-200">Status</th>
            <th class="px-4 py-3 text-left border border-gray-200 w-80">Actions</th>
        </x-slot>

        {{-- BODY --}}
        <x-slot name="body">
            @forelse($categories as $category)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-4 py-3 border border-gray-200 text-center">
                        {{ $category->id }}
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-900 border border-gray-200 text-center">
                        {{ $category->name }}
                    </td>
                    <td class="px-4 py-3 border border-gray-200 text-center">
                        <span style="{{ $category->is_active ? 'background-color:#dcfce7;color:#166534' : 'background-color:#fee2e2;color:#991b1b' }}; padding:4px 12px; border-radius:9999px; font-size:12px; font-weight:500;">
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 border border-gray-200 text-center">
                        <div class="flex gap-2 justify-center">
                            <a href="{{ route('admin.categories.show', $category->id) }}">
                                <x-button>Details</x-button>
                            </a>
                            <a href="{{ route('admin.categories.edit', $category->id) }}">
                                <x-button color="secondary">Edit</x-button>
                            </a>
                            <form method="POST"
                                action="{{ route('admin.categories.destroy', $category->id) }}"
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
                <tr>
                    <td colspan="4" class="text-center py-6 text-gray-500">No categories found</td>
                </tr>
            @endforelse
        </x-slot>
    </x-table>
</x-card>

@endsection