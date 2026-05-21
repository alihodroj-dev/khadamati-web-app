@php
    $municipality = $municipality ?? null;
@endphp

<div class="mb-4">
    <label class="block text-sm font-medium text-gray-700 mb-2">Municipality Name</label>
    <input type="text" 
           name="name" 
           value="{{ old('name', $municipality?->name) }}"
           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
           placeholder="e.g. Beirut Municipality" 
           required>
    @error('name')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Municipality Code</label>
        <input type="text" 
               name="code" 
               value="{{ old('code', $municipality?->code) }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
               placeholder="e.g. BEY-001"
               required>
        <p class="mt-1 text-xs text-gray-500">Unique identifier for the municipality (used for API references)</p>
        @error('code')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
        <input type="email" 
               name="email" 
               value="{{ old('email', $municipality?->email) }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
               placeholder="municipality@example.com">
        @error('email')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
        <input type="text" 
               name="phone" 
               value="{{ old('phone', $municipality?->phone) }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
               placeholder="+961 1 234 567">
        @error('phone')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
        <input type="text" 
               name="address" 
               value="{{ old('address', $municipality?->address) }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
               placeholder="City Center, Main Street 123">
        @error('address')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
        <select name="is_active" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
            <option value="1" @selected((string) old('is_active', $municipality?->is_active ? '1' : '0') === '1')>Active</option>
            <option value="0" @selected((string) old('is_active', $municipality?->is_active ? '1' : '0') === '0')>Inactive</option>
        </select>
        <p class="mt-1 text-xs text-gray-500">Inactive municipalities won't appear in public listings.</p>
        @error('is_active')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>