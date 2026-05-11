@props(['type' => 'button', 'color' => 'primary'])

@php
    $base = "px-4 py-2 rounded-lg font-medium transition";

    $colors = [
        'primary' => 'bg-blue-900 hover:bg-blue-800 text-white',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white',
        'success' => 'bg-green-600 hover:bg-green-700 text-white',
        'secondary' => 'bg-gray-200 hover:bg-gray-300 text-gray-800',
    ];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $base . ' ' . ($colors[$color] ?? $colors['primary'])]) }}>
    {{ $slot }}
</button>