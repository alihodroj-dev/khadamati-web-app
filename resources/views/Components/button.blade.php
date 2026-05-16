@props(['type' => 'button', 'color' => 'primary'])

@php
    $base = "inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-150";

    $colors = [
        'primary'   => 'bg-blue-900 hover:bg-blue-800 text-white',
        'danger'    => 'bg-red-500 hover:bg-red-600 text-white',
        'success'   => 'bg-green-600 hover:bg-green-700 text-white',
        'secondary' => 'bg-white hover:bg-gray-50 text-gray-700',
    ];

    $borders = [
        'primary'   => 'border: 0.5px solid #1e3a5f;',
        'danger'    => 'border: 0.5px solid #ef4444;',
        'success'   => 'border: 0.5px solid #16a34a;',
        'secondary' => 'border: 0.5px solid #e5e7eb;',
    ];
@endphp

<button type="{{ $type }}"
        style="{{ $borders[$color] ?? $borders['primary'] }}"
        {{ $attributes->merge(['class' => $base . ' ' . ($colors[$color] ?? $colors['primary'])]) }}>
    {{ $slot }}
</button>