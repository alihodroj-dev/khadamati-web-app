@props([
    'label' => 'Image URL',
    'name' => 'image_url',
    'value' => null,
])

<div class="mb-6">
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-2">
        {{ $label }}
    </label>

    @if(filled($value))
        <div class="mb-3">
            <img
                src="{{ $value }}"
                alt="Preview"
                class="h-32 w-full max-w-xs rounded-lg border border-gray-200 object-cover bg-gray-50"
                loading="lazy"
            />
        </div>
    @endif

    <input
        type="url"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="https://example.com/image.jpg"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
    />

    <p class="mt-1 text-xs text-gray-500">HTTPS URL to a cover image (used in the iOS app).</p>

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
