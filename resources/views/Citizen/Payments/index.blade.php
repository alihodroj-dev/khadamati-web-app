@extends('layouts.app')

@section('title', 'My Payments')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">My Payments</h1>
        <p class="text-gray-500 mt-1">View all your payment history</p>
    </div>

    @if($payments->count() > 0)
        <div class="bg-white rounded-xl shadow-sm overflow-hidden" style="border: 0.5px solid #e5e7eb;">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($payments as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $payment->transaction_reference }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $payment->serviceRequest->service->name ?? 'Unknown' }}
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                ${{ number_format($payment->amount, 2) }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($payment->status == 'paid') bg-green-100 text-green-800
                                    @elseif($payment->status == 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $payment->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a href="{{ route('citizen.payments.show', $payment->id) }}" class="text-blue-600 hover:underline">
                                    View →
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-6">
            {{ $payments->links() }}
        </div>
        
    @else
        <div class="bg-white rounded-xl p-12 text-center" style="border: 0.5px solid #e5e7eb;">
            <i class="ti ti-credit-card text-gray-300 text-5xl mb-3 block"></i>
            <p class="text-gray-500">No payments yet</p>
            <a href="{{ route('citizen.services.index') }}" class="inline-block mt-3 text-blue-600 hover:underline">
                Browse Services →
            </a>
        </div>
    @endif

</div>
@endsection