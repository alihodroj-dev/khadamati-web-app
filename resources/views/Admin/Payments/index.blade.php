@extends('layouts.app')

@section('content')

<div class="mb-6">

    <h1 class="text-2xl font-bold">
        Payments
    </h1>

    <p class="text-sm text-gray-500">
        Manage all service payments
    </p>

</div>

<x-table>

    <x-slot name="head">

        <th class="px-4 py-3">Reference</th>
        <th class="px-4 py-3">User</th>
        <th class="px-4 py-3">Amount</th>
        <th class="px-4 py-3">Method</th>
        <th class="px-4 py-3">Status</th>
        <th class="px-4 py-3">Actions</th>

    </x-slot>

    <x-slot name="body">

        @forelse($payments as $payment)

            <tr class="border-t">

                <td class="px-4 py-3 font-semibold">
                    {{ $payment->request->reference_number ?? '-' }}
                </td>

                <td class="px-4 py-3">
                    {{ $payment->user->name ?? '-' }}
                </td>

                <td class="px-4 py-3">
                    {{ $payment->amount }} {{ $payment->currency }}
                </td>

                <td class="px-4 py-3">
                    {{ ucfirst($payment->payment_method) }}
                </td>

                <td class="px-4 py-3">

                    <span class="px-2 py-1 text-xs rounded-full
                        @if($payment->status === 'paid') bg-green-100 text-green-700
                        @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-700
                        @else bg-red-100 text-red-700
                        @endif">

                        {{ ucfirst($payment->status) }}

                    </span>

                </td>

                <td class="px-4 py-3">

                    <a href="{{ route('admin.payments.show', $payment->id) }}"
                       class="text-blue-600 hover:underline">

                        View

                    </a>

                </td>

            </tr>

        @empty

            <tr>
                <td colspan="6" class="text-center py-6 text-gray-500">
                    No payments found
                </td>
            </tr>

        @endforelse

    </x-slot>

</x-table>

<div class="mt-4">
    {{ $payments->links() }}
</div>

@endsection