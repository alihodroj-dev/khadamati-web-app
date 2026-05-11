@props(['type' => 'success'])

@php
    $colors = [
        'success' => 'bg-green-100 text-green-800',
        'error' => 'bg-red-100 text-red-800',
        'warning' => 'bg-yellow-100 text-yellow-800',
        'info' => 'bg-blue-100 text-blue-800',
    ];
@endphp

<div class="px-4 py-3 rounded-lg mb-4 {{ $colors[$type] ?? $colors['success'] }}">
    {{ $slot }}
</div>