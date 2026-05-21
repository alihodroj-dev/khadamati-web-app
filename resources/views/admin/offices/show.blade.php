@extends('layouts.app')

@section('content')

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
            <a href="{{ route('admin.offices.index') }}" class="hover:text-blue-600 transition">Offices</a>
            <span>/</span>
            <span>Details</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">
            Office Details <span class="text-gray-400 font-mono text-lg ml-2">#{{ $office->id }}</span>
        </h1>
    </div>

    <div class="flex gap-3">
        <a href="{{ route('admin.offices.index') }}" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
            Back
        </a>
        <a href="{{ route('admin.offices.edit', $office->id) }}">
            <x-button color="secondary">Edit Office</x-button>
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
    <div class="lg:col-span-2 space-y-8">
        <x-card class="h-fit">
            <h2 class="text-lg font-semibold mb-6">Office Information</h2>
            <div class="space-y-6 leading-relaxed">
                <div>
                    <p class="text-sm text-gray-500">Name</p>
                    <p class="text-base font-medium">{{ $office->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Municipality</p>
                    <p class="text-base text-gray-700">{{ $office->municipality?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Address</p>
                    <p class="text-base text-gray-700">{{ $office->address }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Phone</p>
                    <p class="text-base text-gray-700">{{ $office->phone ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Email</p>
                    <p class="text-base text-gray-700">{{ $office->email ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Cover Image</p>
                    @if($office->image_url)
                        <img src="{{ $office->image_url }}" alt="{{ $office->name }}" class="mt-2 h-40 w-full max-w-md rounded-lg border border-gray-200 object-cover">
                    @else
                        <p class="text-base text-gray-700">—</p>
                    @endif
                </div>
                <div>
                    <p class="text-sm text-gray-500">Coordinates</p>
                    <p class="text-base text-gray-700">
                        @if($office->latitude !== null && $office->longitude !== null)
                            {{ $office->latitude }}, {{ $office->longitude }}
                        @else
                            —
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Working Hours</p>
                    <pre class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg overflow-x-auto">{{ $office->working_hours ? json_encode($office->working_hours, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '—' }}</pre>
                </div>
            </div>
        </x-card>
    </div>

    <div class="space-y-8">
        <x-card class="h-fit">
            <h2 class="text-lg font-semibold mb-6">Status</h2>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Office Status</span>
                    <span class="px-3 py-1 rounded-full text-xs {{ $office->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $office->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Service Requests</span>
                    <span class="text-gray-900 font-medium">{{ $office->service_requests_count }}</span>
                </div>
            </div>
        </x-card>
    </div>
</div>

@endsection
