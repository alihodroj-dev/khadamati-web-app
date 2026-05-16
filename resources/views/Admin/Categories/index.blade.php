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
            <tr class="border-t hover:bg-gray-50">
                <td class="px-4 py-3 border border-gray-200 text-center">
                    1
                </td>
                <td class="px-4 py-3 font-medium text-gray-900 border border-gray-200 text-center">
                    Home Services
                </td>
                <td class="px-4 py-3 border border-gray-200 text-center">
                    <span style="background-color: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: 500;">
                        Admin
                    </span>
                </td>
                <td class="px-4 py-3 border border-gray-200 text-center">
                    <div class="flex gap-2 justify-center">
                        <a href="{{ route('admin.categories.show', 1) }}">
                            <x-button>Details</x-button>
                        </a>

                        <a href="{{ route('admin.categories.edit', 1) }}">
                            <x-button color="secondary">Edit</x-button>
                        </a>

                        <form method="POST"
                              action="{{ route('admin.categories.destroy', 1) }}"
                              onsubmit="return confirm('Are you sure you want to delete this category?')"
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                style="background-color: #ef4444; color: white; padding: 8px 16px; border-radius: 8px; border: none; cursor: pointer;"
                            >
                                Delete
                            </button>
                        </form>
                    </div>
                </tr>
            </table>
        </x-slot>
    </x-table>
</x-card>

@endsection