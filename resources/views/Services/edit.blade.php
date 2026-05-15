@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-bold mb-6">
    Edit Service #{{ $id }}
</h1>

<x-card>

    <form method="POST" action="{{ route('services.update', $id) }}">

        @csrf
        @method('PUT')

        <x-input label="Category ID" name="service_category_id" value="1" />

        <x-input label="Service Name" name="name" value="Passport Renewal" />

        <x-input label="Description" name="description" value="Renew passport service" />

        <x-input label="Base Fee" name="base_fee" value="20" />

        <x-input label="Estimated Processing Days" name="estimated_processing_days" value="5" />

        <x-input label="Required Documents" name="required_documents" value="ID Copy, Photos" />

        {{-- Requires Appointment --}}
        <div class="mb-4">

            <label class="block text-sm font-medium text-gray-700 mb-2">
                Requires Appointment
            </label>

            <select name="requires_appointment"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg">

                <option value="1" selected>Yes</option>
                <option value="0">No</option>

            </select>

        </div>

        {{-- Status --}}
        <div class="mb-6">

            <label class="block text-sm font-medium text-gray-700 mb-2">
                Status
            </label>

            <select name="is_active"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg">

                <option value="1" selected>Active</option>
                <option value="0">Inactive</option>

            </select>

        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between mt-6">
            <a 
                href="{{ route('services.index') }}" 
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