@extends('layouts.app')

@section('content')

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
            <a href="{{ route('admin.categories.index') }}" class="hover:text-blue-600 transition">Categories</a>
            <span>/</span>
            <span>Details</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">
            Category Details <span class="text-gray-400 font-mono text-lg ml-2">#{{ $id }}</span>
        </h1>
    </div>

    <div class="flex gap-3">
        <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
            Back
        </a>
        <a href="{{ route('admin.categories.edit', $id) }}">
            <x-button color="secondary">
                Edit Category
            </x-button>
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

    {{-- MAIN INFO --}}
    <div class="lg:col-span-2 space-y-8">

        <x-card class="h-fit">

            <h2 class="text-lg font-semibold mb-6">
                Category Information
            </h2>

            <div class="space-y-6 leading-relaxed">

                <div>
                    <p class="text-sm text-gray-500">Name</p>
                    <p class="text-base font-medium">Home Services</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Description</p>
                    <p class="text-base text-gray-700">
                        Services related to household maintenance and repairs.
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Icon</p>
                    <p class="text-base text-gray-700 text-2xl">
                        🏠
                    </p>
                </div>

            </div>

        </x-card>

    </div>

    {{-- SIDE INFO --}}
    <div class="space-y-8">

        <x-card class="h-fit">

            <h2 class="text-lg font-semibold mb-6">
                Status
            </h2>

            <div class="flex justify-between items-center">

                <span class="text-gray-600">Category Status</span>

                <span class="px-3 py-1 rounded-full bg-green-100 text-green-800 text-xs">
                    Active
                </span>

            </div>

        </x-card>

    </div>

</div>

@endsection