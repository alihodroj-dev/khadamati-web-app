@extends('layouts.app')

@section('content')

<div class="mb-6">

    <h1 class="text-2xl font-bold">
        My Assigned Requests
    </h1>

    <p class="text-sm text-gray-500">
        Manage your assigned service requests
    </p>

</div>

<x-table>

    <x-slot name="head">
        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Reference</th>
        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Service</th>
        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Status</th>
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
                    {{ ucfirst($req->status) }}
                </td>

                <td class="px-4 py-4 text-sm text-gray-700">
                    @if($req->payment && $req->payment->status === 'paid')
                        <span class="text-green-600">Paid</span>
                    @else
                        <span class="text-yellow-600">Pending</span>
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
                <td class="px-4 py-4 text-sm text-gray-700">
                    No assigned requests
                </td>
            </tr>

        @endforelse

    </x-slot>

</x-table>

<div class="mt-4">
    {{ $requests->links() }}
</div>

@endsection