@extends('layouts.app')

@section('content')

<div class="mb-6 flex justify-between items-center">

    <div>
        <h1 class="text-2xl font-bold">
            My Appointments
        </h1>
        <p class="text-sm text-gray-500">
            All your scheduled appointments
        </p>
    </div>

    <a href="{{ route('staff.appointments.today') }}"
       class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
        Today's Schedule
    </a>

</div>

<x-table>

    <x-slot name="head">

        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Reference</th>
        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Date</th>
        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Time</th>
        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Status</th>
        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Actions</th>

    </x-slot>

    <x-slot name="body">

        @forelse($appointments as $app)

    <tr class="hover:bg-gray-50 transition-colors">

        <td class="px-4 py-4 text-sm text-gray-700">
            {{ $app->serviceRequest->reference_number ?? '-' }}
        </td>

        <td class="px-4 py-4 text-sm text-gray-700">
            {{ $app->appointment_date }}
        </td>

        <td class="px-4 py-4 text-sm text-gray-700">
            {{ $app->appointment_time }}
        </td>

        <td class="px-4 py-4 text-sm text-gray-700">
            {{ ucfirst($app->status) }}
        </td>

        <td class="px-4 py-4 text-sm text-gray-700">
            <a href="{{ route('staff.appointments.show', $app->id) }}"
               class="text-blue-600 hover:underline">
                View
            </a>
        </td>

    </tr>

    @empty
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-4 py-4 text-sm text-gray-700">
                No appointments found
            </td>
        </tr>
    @endforelse

    </x-slot>

</x-table>

@endsection