@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-bold mb-6">
    Edit Service #{{ $service->id }}
</h1>

<x-card>

    <form method="POST" action="{{ route('admin.services.update', $service->id) }}">

        @csrf
        @method('PUT')

        <x-input label="Service Name" name="name" value="{{ $service->name }}" />
        <x-input label="Description" name="description" value="{{ $service->description }}" />
        <x-input label="Base Fee ($)" name="base_fee" value="{{ $service->base_fee }}" />
        <x-input label="Estimated Processing Days" name="estimated_processing_days" value="{{ $service->estimated_processing_days }}" />
        <x-input label="Required Documents" name="required_documents" value="{{ implode(',', $service->required_documents ?? []) }}" />

        {{-- Requires Appointment --}}
        <div class="mb-4">

            <label class="block text-sm font-medium text-gray-700 mb-2">
                Requires Appointment
            </label>

            <select name="requires_appointment" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="1" {{ $service->requires_appointment ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ !$service->requires_appointment ? 'selected' : '' }}>No</option>
            </select>

        </div>

        {{-- Category --}}
        <div class="mb-4">

            <label class="block text-sm font-medium text-gray-700 mb-2">
                Category
            </label>

            <select name="service_category_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ $service->service_category_id == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Status --}}
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Status
            </label>

            <select name="is_active" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="1" {{ $service->is_active ? 'selected' : '' }}>Active</option>
                <option value="0" {{ !$service->is_active ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between mt-6">
            <a 
                href="{{ route('admin.services.index') }}" 
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out"
            >
                Back to Services
            </a>

            <x-button type="submit">
                Update Service
            </x-button>
        </div>

    </form>

</x-card>

@endsection