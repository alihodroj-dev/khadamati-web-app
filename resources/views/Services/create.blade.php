@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-bold mb-6 text-gray-900">
    Create Service
</h1>

<x-card>

    <form method="POST" action="{{ route('services.store') }}">

        @csrf

        {{-- Category Selection --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Service Category
            </label>
            <select 
                name="service_category_id" 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
            >
                <option value="" disabled selected>Select a category...</option>
                
                {{-- These will be replaced by your @foreach($categories as $category) loop later --}}
                <option value="1">Civil Services</option>
                <option value="2">Home Services</option>
                <option value="3">Health & Safety</option>
                <option value="4">Education</option>
            </select>
        </div>

        <x-input label="Service Name" name="name" placeholder="e.g. Passport Renewal" />

        <x-input label="Description" name="description" placeholder="Briefly describe the service..." />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-input label="Base Fee ($)" name="base_fee" type="number" step="0.01" />
            <x-input label="Estimated Processing Days" name="estimated_processing_days" type="number" />
        </div>

        <x-input label="Required Documents" name="required_documents" placeholder="e.g. ID, Proof of Address" />

        {{-- Requires Appointment & Status Row --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Requires Appointment
                </label>
                <select name="requires_appointment"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Status
                </label>
                <select name="is_active"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between mt-6 pt-6 ">
            <a 
                href="{{ route('services.index') }}" 
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out"
            >
                Back to Services
            </a>

            <x-button type="submit">
                Save Service
            </x-button>
        </div>

    </form>

</x-card>

@endsection