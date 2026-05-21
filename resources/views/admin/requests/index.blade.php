@extends('layouts.app')

@section('content')

@php
    use App\Support\ServiceRequestStatus;
@endphp

<div class="flex justify-between items-center mb-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">
            Service Requests
        </h1>

        <p class="text-sm text-gray-500">
            Manage all submitted requests
        </p>
    </div>

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
        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Office</th>
        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Status</th>
        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Staff</th>
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
                    {{ $req->office?->name ?? '—' }}
                </td>

                <td class="px-4 py-4 text-sm text-gray-700">

                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                        @if($req->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($req->status === 'under_review') bg-blue-100 text-blue-800
                        @elseif($req->status === 'approved') bg-green-100 text-green-800
                        @elseif($req->status === 'rejected') bg-red-100 text-red-800
                        @elseif($req->status === 'requires_action') bg-indigo-100 text-indigo-800
                        @elseif($req->status === 'cancelled') bg-gray-100 text-gray-700
                        @else bg-gray-100 text-gray-700
                        @endif">

                        {{ ucfirst(str_replace('_', ' ', $req->status)) }}

                    </span>

                </td>

                <td class="px-4 py-4 text-sm text-gray-700">
                    {{ $req->assignedStaff->name ?? 'Not assigned' }}
                </td>

                <td class="px-4 py-4 text-sm text-gray-700">

                    <a href="{{ route('admin.requests.show', $req->id) }}"
                       class="text-blue-600 hover:underline text-sm font-medium">
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
