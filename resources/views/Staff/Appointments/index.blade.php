@extends('layouts.app')

@section('content')

<div class="mb-6">

    <h1 class="text-2xl font-bold">
        My Appointments
    </h1>

</div>

<x-table>

    <x-slot name="head">

        <th class="px-4 py-3">Reference</th>
        <th class="px-4 py-3">Date</th>
        <th class="px-4 py-3">Time</th>
        <th class="px-4 py-3">Status</th>
        <th class="px-4 py-3">Actions</th>

    </x-slot>

    <x-slot name="body">

        @forelse($appointments as $app)

    <tr class="border-t">

        <td class="px-4 py-3">
            {{ $app->serviceRequest->reference_number ?? '-' }}
        </td>

        <td class="px-4 py-3">
            {{ $app->appointment_date }}
        </td>

        <td class="px-4 py-3">
            {{ $app->appointment_time }}
        </td>

        <td class="px-4 py-3">
            {{ ucfirst($app->status) }}
        </td>

        <td class="px-4 py-3">
            <a href="{{ route('staff.appointments.show', $app->id) }}"
               class="text-blue-600 hover:underline">
                View
            </a>
        </td>

    </tr>

@empty

    {{-- FAKE TEST DATA --}}
    <tr class="border-t bg-gray-50">

        <td class="px-4 py-3 font-semibold">
            REF-2026-001
        </td>

        <td class="px-4 py-3">
            2026-05-20
        </td>

        <td class="px-4 py-3">
            10:30 AM
        </td>

        <td class="px-4 py-3">
            <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">
                Scheduled
            </span>
        </td>

        <td class="px-4 py-3">
            <span class="text-gray-400 text-sm">
                View (disabled - demo)
            </span>
        </td>

    </tr>

    <tr class="border-t">

        <td class="px-4 py-3 font-semibold">
            REF-2026-002
        </td>

        <td class="px-4 py-3">
            2026-05-22
        </td>

        <td class="px-4 py-3">
            02:00 PM
        </td>

        <td class="px-4 py-3">
            <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">
                Completed
            </span>
        </td>

        <td class="px-4 py-3">
            <span class="text-gray-400 text-sm">
                View (disabled - demo)
            </span>
        </td>

    </tr>

@endforelse

    </x-slot>

</x-table>

@endsection