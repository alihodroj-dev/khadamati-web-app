@extends('layouts.app')

@section('content')

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

{{-- FILTER --}}
<form method="GET" class="mb-4 flex gap-3">

    <select name="status" class="border rounded-lg px-3 py-2 text-sm">

        <option value="">All Status</option>
        <option value="pending">Pending</option>
        <option value="under_review">Under Review</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
        <option value="requires_action">Requires Action</option>
        <option value="completed">Completed</option>

    </select>

    <button class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
        Filter
    </button>

</form>

{{-- TABLE --}}
<x-table>

    <x-slot name="head">

        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Reference</th>
        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Service</th>
        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Status</th>
        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Staff</th>
        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Actions</th>

    </x-slot>

    <x-slot name="body">

        @forelse($requests as $req)

            <tr class="hover:bg-gray-50 transition-colors">

                {{-- Reference --}}
                <td class="px-4 py-4 text-sm text-gray-700">
                    {{ $req->reference_number }}
                </td>

                {{-- Service --}}
                <td class="px-4 py-4 text-sm text-gray-700">
                    {{ $req->service->name ?? '-' }}
                </td>

                {{-- Status --}}
                <td class="px-4 py-4 text-sm text-gray-700">

                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                        @if($req->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($req->status === 'under_review') bg-blue-100 text-blue-800
                        @elseif($req->status === 'approved') bg-green-100 text-green-800
                        @elseif($req->status === 'rejected') bg-red-100 text-red-800
                        @elseif($req->status === 'requires_action') bg-indigo-100 text-indigo-800
                        @else bg-gray-100 text-gray-700
                        @endif">

                        {{ ucfirst(str_replace('_', ' ', $req->status)) }}

                    </span>

                </td>

                {{-- Staff --}}
                <td class="px-4 py-4 text-sm text-gray-700">
                    {{ $req->assignedStaff->name ?? 'Not assigned' }}
                </td>

                {{-- Actions (3) --}}
                <td class="px-4 py-4 text-sm text-gray-700">

                    {{-- 1. VIEW --}}
                    <a href="{{ route('admin.requests.show', $req->id) }}"
                       class="text-blue-600 hover:underline text-sm font-medium">
                        View
                    </a>

                    {{-- 2. ASSIGN  --}}
                    <a href="{{ route('admin.requests.show', $req->id) }}"
                    class="text-indigo-600 hover:underline text-sm">
                        Assign
                    </a>

                    {{-- 3. DELETE --}}
                    <form method="POST"
                        action="{{ route('admin.requests.destroy', $req->id) }}"
                        onsubmit="return confirm('Are you sure you want to delete this request?')"
                        class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background-color: #ef4444; color: white; padding: 8px 16px; border-radius: 8px; border: none; cursor: pointer;">
                            Delete
                        </button>
                    </form>

                </td>

            </tr>

        @empty

            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-4 text-sm text-gray-700">
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