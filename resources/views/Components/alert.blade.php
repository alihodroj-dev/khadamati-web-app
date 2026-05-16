@props(['type' => 'success'])

@php
    $styles = [
        'success' => ['bg' => '#f0fdf4', 'text' => '#166534', 'border' => '#bbf7d0', 'icon' => 'ti-circle-check'],
        'error'   => ['bg' => '#fef2f2', 'text' => '#991b1b', 'border' => '#fecaca', 'icon' => 'ti-circle-x'],
        'warning' => ['bg' => '#fffbeb', 'text' => '#92400e', 'border' => '#fde68a', 'icon' => 'ti-alert-triangle'],
        'info'    => ['bg' => '#eff6ff', 'text' => '#1e40af', 'border' => '#bfdbfe', 'icon' => 'ti-info-circle'],
    ];

    $s = $styles[$type] ?? $styles['success'];
@endphp

<div class="flex items-start gap-3 px-4 py-3 rounded-lg mb-4 text-sm"
     style="background: {{ $s['bg'] }}; color: {{ $s['text'] }}; border: 0.5px solid {{ $s['border'] }};">
    <i class="ti {{ $s['icon'] }} text-lg flex-shrink-0 mt-0.5" aria-hidden="true"></i>
    <span>{{ $slot }}</span>
</div>