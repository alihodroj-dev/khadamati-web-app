@extends('layouts.app')

@section('content')

@php
    use App\Support\ServiceRequestStatus;
@endphp

<div class="mb-6">

    <h1 class="text-2xl font-bold">
        Office Requests
    </h1>

    <p class="text-sm text-gray-500">
        Incoming requests for your office (assigned and unassigned)
    </p>

</div>

<form method="GET" class="mb-4 flex flex-wrap gap-3">

    <select name="status" class="border rounded-lg px-3 py-2 text-sm">
        <option value="">All Status</option>
        @foreach(ServiceRequestStatus::all() as $status)
            <option value="{{ $status }}" @selected(request('status') === $status)>
                {{ ucfirst(str_replace('_', ' ', $status)) }}
            </option>
        @endforeach
    </select>

    <select name="assignment" class="border rounded-lg px-3 py-2 text-sm">
        <option value="">All Assignments</option>
        <option value="unassigned" @selected(request('assignment') === 'unassigned')>Unassigned</option>
        <option value="assigned" @selected(request('assignment') === 'assigned')>Assigned</option>
    </select>

    <button class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
        Filter
    </button>

</form>

<x-table>

    <x-slot name="head">
        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Reference</th>
        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Service</th>
        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Status</th>
        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Assigned</th>
        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Payment</th>
        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Actions</th>
    </x-slot>

    <x-slot name="body">

        @forelse($requests as $req)

            <tr class="hover:bg-gray-50 transition-colors">

                <td class="px-4 py-4 text-sm text-gray-700">
                    {{ $req->reference_number }}
                </td>

                <td class="px-4 py-4 text-sm text-gray-700">
                    {{ $req->service->name ?? '-' }}
                </td>

                <td class="px-4 py-4 text-sm text-gray-700">
                    {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                </td>

                <td class="px-4 py-4 text-sm text-gray-700">
                    {{ $req->assignedStaff->name ?? 'Unassigned' }}
                </td>

                <td class="px-4 py-4 text-sm text-gray-700">
                    @if($req->payment && $req->payment->status === 'paid')
                        <span class="text-green-600">Paid</span>
                    @elseif($req->payment)
                        <span class="text-yellow-600">Pending</span>
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </td>

                <td class="px-4 py-4 text-sm text-gray-700">
                    <a href="{{ route('staff.requests.show', $req->id) }}"
                       class="text-blue-600 hover:underline">
                        View
                    </a>
                </td>

            </tr>

        @empty

            <tr class="hover:bg-gray-50 transition-colors">
                <td colspan="6" class="px-4 py-4 text-sm text-gray-700">
                    No requests found
                </td>
            </tr>

        @endforelse

    </x-slot>

</x-table>

<div class="mt-4">
    {{ $requests->links() }}
</div>

@endsection
