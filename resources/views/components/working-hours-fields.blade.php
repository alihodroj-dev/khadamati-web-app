@props(['workingHours' => null])

@php
    $days = [
        'monday' => 'Monday',
        'tuesday' => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday' => 'Thursday',
        'friday' => 'Friday',
        'saturday' => 'Saturday',
        'sunday' => 'Sunday',
    ];

    $hours = is_array($workingHours) ? $workingHours : [];
@endphp

<div class="mb-6">
    <label class="block text-sm font-medium text-gray-700 mb-3">Working Hours</label>

    <div class="space-y-3">
        @foreach($days as $day => $label)
            @php
                $existing = $hours[$day] ?? null;
                $enabled = old("working_hours.$day.enabled", $existing ? '1' : null);
                $start = old("working_hours.$day.start", is_array($existing) ? ($existing[0] ?? '') : '');
                $end = old("working_hours.$day.end", is_array($existing) ? ($existing[1] ?? '') : '');
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-[120px_1fr_1fr] gap-3 items-center">
                <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                    <input
                        type="checkbox"
                        name="working_hours[{{ $day }}][enabled]"
                        value="1"
                        @checked((string) $enabled === '1')
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    >
                    {{ $label }}
                </label>

                <input
                    type="time"
                    name="working_hours[{{ $day }}][start]"
                    value="{{ $start }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    aria-label="{{ $label }} opening time"
                >

                <input
                    type="time"
                    name="working_hours[{{ $day }}][end]"
                    value="{{ $end }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    aria-label="{{ $label }} closing time"
                >
            </div>
        @endforeach
    </div>

    <p class="mt-2 text-xs text-gray-500">Check each open day and choose its opening and closing time.</p>

    @error('working_hours')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>
