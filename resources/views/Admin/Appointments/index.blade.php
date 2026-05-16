@extends('layouts.app')

@section('content')

<div class="mb-6">

    <h1 class="text-2xl font-bold">
        Appointments
    </h1>

    <p class="text-sm text-gray-500">
        Manage all scheduled appointments
    </p>

</div>

<x-table>

    <x-slot name="head">

        <th class="px-4 py-3">Reference</th>
        <th class="px-4 py-3">User</th>
        <th class="px-4 py-3">Staff</th>
        <th class="px-4 py-3">Date</th>
        <th class="px-4 py-3">Time</th>
        <th class="px-4 py-3">Status</th>
        <th class="px-4 py-3">Actions</th>

    </x-slot>

    <x-slot name="body">

        @forelse($appointments as $app)

            <tr class="border-t">

                <td class="px-4 py-3 font-semibold">
                    {{ $app->serviceRequest->reference_number ?? '-' }}
                </td>

                <td class="px-4 py-3">
                    {{ $app->user->name ?? '-' }}
                </td>

                <td class="px-4 py-3">
                    {{ $app->staff->name ?? 'Not assigned' }}
                </td>

                <td class="px-4 py-3">
                    {{ $app->appointment_date }}
                </td>

                <td class="px-4 py-3">
                    {{ $app->appointment_time }}
                </td>

                <td class="px-4 py-3">
                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">
                        {{ ucfirst($app->status) }}
                    </span>
                </td>

                <td class="px-4 py-3 flex gap-3">

                    <a href="{{ route('admin.appointments.show', $app->id) }}"
                       class="text-blue-600 hover:underline">
                        View
                    </a>

                    <a href="{{ route('admin.appointments.edit', $app->id) }}"
                       class="text-indigo-600 hover:underline">
                        Edit
                    </a>

                    <form method="POST"
                          action="{{ route('admin.appointments.destroy', $app->id) }}">

                        @csrf
                        @method('DELETE')

                        <button class="text-red-600 hover:underline">
                            Delete
                        </button>

                    </form>

                </td>

            </tr>

        @empty

            <tr>
                <td colspan="7" class="text-center py-6 text-gray-500">
                    No appointments found
                </td>
            </tr>

        @endforelse

    </x-slot>

</x-table>

<div class="mt-4">
    {{ $appointments->links() }}
</div>

@endsection