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
        <th class="px-4 py-3 text-left">Reference</th>
        <th class="px-4 py-3 text-left">Service</th>
        <th class="px-4 py-3 text-left">Status</th>
        <th class="px-4 py-3 text-left">Payment</th>
        <th class="px-4 py-3 text-left">Actions</th>
    </x-slot>

    <x-slot name="body">

        @forelse($requests as $req)

            <tr class="border-t hover:bg-gray-50">

                <td class="px-4 py-3 font-semibold">
                    {{ $req->reference_number }}
                </td>

                <td class="px-4 py-3">
                    {{ $req->service->name ?? '-' }}
                </td>

                <td class="px-4 py-3">
                    {{ ucfirst($req->status) }}
                </td>

                <td class="px-4 py-3">
                    @if($req->payment && $req->payment->status === 'paid')
                        <span class="text-green-600">Paid</span>
                    @else
                        <span class="text-yellow-600">Pending</span>
                    @endif
                </td>


                <td class="px-4 py-3">

                    <a href="{{ route('staff.requests.show', $req->id) }}"
                       class="text-blue-600 hover:underline">
                        View
                    </a>

                </td>

            </tr>

        @empty

            <tr>
                <td colspan="4" class="text-center py-6 text-gray-500">
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