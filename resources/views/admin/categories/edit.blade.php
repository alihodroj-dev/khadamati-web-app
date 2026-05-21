@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-bold mb-6">
    Edit Category #{{ $category->id }}
</h1>

<x-card>

    <form method="POST" action="{{ route('admin.categories.update', $category->id) }}">
        @csrf
        @method('PUT')

        <x-input label="Category Name" name="name" value="{{ $category->name }}" />
        <x-input label="Description" name="description" value="{{ $category->description }}" />
        <x-input label="Icon" name="icon" value="{{ old('icon', $category->icon) }}" />
        <x-image-url-field :value="old('image_url', $category->image_url)" />

        {{-- Status --}}
        <div class="mb-6">

            <label class="block text-sm font-medium text-gray-700 mb-2">
                Status
            </label>

            <select name="is_active" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="1" {{ $category->is_active ? 'selected' : '' }}>Active</option>
                <option value="0" {{ !$category->is_active ? 'selected' : '' }}>Inactive</option>
            </select>

        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between mt-6">
            <a 
                href="{{ route('admin.categories.index') }}" 
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