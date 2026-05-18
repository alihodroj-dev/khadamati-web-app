@extends('layouts.app')

@section('title', 'Payment Details')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
    <div class="mb-6">
        <a href="{{ route('citizen.payments.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
            <i class="ti ti-arrow-left"></i>
            <span>Back to Payments</span>
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden" style="border: 0.5px solid #e5e7eb;">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-8 text-white text-center">
            @if($payment->status == 'paid')
                <i class="ti ti-circle-check text-5xl mb-3 block"></i>
                <h1 class="text-2xl font-bold">Payment Successful!</h1>
                <p class="text-blue-100 mt-1">Thank you for your payment</p>
            @else
                <i class="ti ti-clock text-5xl mb-3 block"></i>
                <h1 class="text-2xl font-bold">Payment Pending</h1>
                <p class="text-blue-100 mt-1">Complete your payment to proceed</p>
            @endif
        </div>
        
        <!-- Payment Details -->
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div>
                    <h3 class="font-semibold text-gray-900 mb-3">Payment Information</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Transaction ID:</span>
                            <span class="font-mono">{{ $payment->transaction_reference }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Amount:</span>
                            <span class="font-bold text-lg text-blue-900">${{ number_format($payment->amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Status:</span>
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($payment->status == 'paid') bg-green-100 text-green-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Payment Method:</span>
                            <span>{{ ucfirst($payment->payment_method) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Date:</span>
                            <span>{{ $payment->created_at->format('M d, Y h:i A') }}</span>
                        </div>
                        @if($payment->paid_at)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Paid At:</span>
                            <span>{{ $payment->paid_at->format('M d, Y h:i A') }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                
                <div>
                    <h3 class="font-semibold text-gray-900 mb-3">Service Information</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Service:</span>
                            <span>{{ $payment->serviceRequest->service->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Request Reference:</span>
                            <span>{{ $payment->serviceRequest->reference_number ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Request Status:</span>
                            <span>{{ ucfirst($payment->serviceRequest->status ?? 'N/A') }}</span>
                        </div>
                    </div>
                </div>
                
            </div>
            
            <!-- Action Buttons -->
            <div class="flex gap-3 mt-8 pt-6 border-t">
                @if($payment->status == 'paid')
                    <a href="{{ route('citizen.requests.show', $payment->serviceRequest->id) }}" 
                       class="flex-1 text-center px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800 transition">
                        View Request
                    </a>
                @else
                    <a href="{{ route('citizen.payments.checkout', $payment->serviceRequest->id) }}" 
                       class="flex-1 text-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        Retry Payment
                    </a>
                @endif
                
                <a href="{{ route('citizen.payments.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Back to Payments
                </a>
            </div>
            
        </div>
        
    </div>
</div>
@endsection