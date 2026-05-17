@php
    $office = $office ?? null;
    $workingHoursValue = old(
        'working_hours',
        $office?->working_hours ? json_encode($office->working_hours, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : ''
    );
@endphp

<div class="mb-6">
    <label class="block text-sm font-medium text-gray-700 mb-2">Municipality</label>
    <select name="municipality_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        <option value="">— None —</option>
        @foreach($municipalities as $municipality)
            <option value="{{ $municipality->id }}"
                @selected((string) old('municipality_id', $office?->municipality_id) === (string) $municipality->id)>
                {{ $municipality->name }}
            </option>
        @endforeach
    </select>
</div>

<x-input label="Office Name" name="name" value="{{ old('name', $office?->name) }}" />
<x-input label="Address" name="address" value="{{ old('address', $office?->address) }}" />
<x-input label="Phone" name="phone" value="{{ old('phone', $office?->phone) }}" />
<x-input label="Email" name="email" type="email" value="{{ old('email', $office?->email) }}" />
<x-input label="Latitude" name="latitude" value="{{ old('latitude', $office?->latitude) }}" />
<x-input label="Longitude" name="longitude" value="{{ old('longitude', $office?->longitude) }}" />

<div class="mb-6">
    <label class="block text-sm font-medium text-gray-700 mb-2">Working Hours (JSON)</label>
    <textarea
        name="working_hours"
        rows="6"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg font-mono text-sm focus:ring-2 focus:ring-blue-500"
        placeholder='{"monday":["09:00","17:00"]}'
    >{{ $workingHoursValue }}</textarea>
    <p class="mt-1 text-xs text-gray-500">Optional. Provide a JSON object for weekly hours.</p>
</div>

<div class="mb-6">
    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
    <select name="is_active" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        <option value="1" @selected((string) old('is_active', $office?->is_active ? '1' : '0') === '1')>Active</option>
        <option value="0" @selected((string) old('is_active', $office?->is_active ? '1' : '0') === '0')>Inactive</option>
    </select>
</div>
