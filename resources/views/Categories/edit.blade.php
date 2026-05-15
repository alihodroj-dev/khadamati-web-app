@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-bold mb-6">
    Edit Category #{{ $id }}
</h1>

<x-card>

    <form method="POST" action="{{ route('categories.update', $id) }}">
        @csrf
        @method('PUT')

        <x-input
            label="Category Name"
            name="name"
            value="Example Category"
        />

        <x-input
            label="Description"
            name="description"
            value="Services related to household maintenance"
        />

        <x-input
            label="Icon"
            name="icon"
            value="🏠"
        />

        {{-- Status --}}
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
                href="{{ route('categories.index') }}" 
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out"
            >
                Back to Categories
            </a>

            <x-button type="submit">
                Update Category
            </x-button>
        </div>
    </form>

</x-card>

@endsection