@extends('layouts.app')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Office Profile</h1>
    <p class="text-sm text-gray-500">
        Update contact details, location coordinates, and working hours for your office.
    </p>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
        {{ session('error') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border p-6 max-w-2xl">

    <form method="POST" action="{{ route('staff.office.update') }}">
        @csrf
        @method('PUT')

        <x-input label="Office Name" name="name" value="{{ old('name', $office->name) }}" required />
        <x-input label="Address" name="address" value="{{ old('address', $office->address) }}" required />
        <x-input label="Phone" name="phone" value="{{ old('phone', $office->phone) }}" />
        <x-input label="Email" name="email" type="email" value="{{ old('email', $office->email) }}" />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input
                label="Latitude"
                name="latitude"
                value="{{ old('latitude', $office->latitude) }}"
                placeholder="e.g. 33.8938"
            />
            <x-input
                label="Longitude"
                name="longitude"
                value="{{ old('longitude', $office->longitude) }}"
                placeholder="e.g. 35.5018"
            />
        </div>

        <p class="text-xs text-gray-500 -mt-4 mb-6">
            Optional map coordinates (latitude −90 to 90, longitude −180 to 180). No map integration — values are stored only.
        </p>

        @php
            $workingHoursValue = old(
                'working_hours',
                $office->working_hours
                    ? json_encode($office->working_hours, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                    : ''
            );
        @endphp

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Working Hours (JSON)</label>
            <textarea
                name="working_hours"
                rows="6"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg font-mono text-sm focus:ring-2 focus:ring-blue-500"
                placeholder='{"monday":["09:00","17:00"]}'
            >{{ $workingHoursValue }}</textarea>
            <p class="mt-1 text-xs text-gray-500">Optional weekly schedule as a JSON object.</p>
            @error('working_hours')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        @if($office->municipality)
            <p class="text-sm text-gray-500 mb-6">
                <span class="font-medium text-gray-700">Municipality:</span>
                {{ $office->municipality->name }}
                <span class="text-xs">(managed by admin)</span>
            </p>
        @endif

        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-blue-700">
            Save Office Profile
        </button>
    </form>

</div>

@endsection
