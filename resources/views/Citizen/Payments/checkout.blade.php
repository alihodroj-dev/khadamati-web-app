@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
    <div class="mb-6">
        <a href="{{ route('citizen.requests.show', $serviceRequest->id) }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
            <i class="ti ti-arrow-left"></i>
            <span>Back to Request</span>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Payment Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl p-6 shadow-sm" style="border: 0.5px solid #e5e7eb;">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Payment Details</h2>
                
                <form method="POST" action="{{ route('citizen.payments.process', $payment->id) }}" id="paymentForm">
                    @csrf
                    
                    <!-- Payment Method Selection -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Select Payment Method</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="border rounded-lg p-3 cursor-pointer hover:bg-gray-50 transition" style="border-color: #e5e7eb;">
                                <input type="radio" name="payment_method" value="card" class="mr-2" checked onchange="togglePaymentMethod()">
                                <i class="ti ti-credit-card text-blue-600"></i>
                                <span class="ml-1">Credit/Debit Card</span>
                            </label>
                            <label class="border rounded-lg p-3 cursor-pointer hover:bg-gray-50 transition" style="border-color: #e5e7eb;">
                                <input type="radio" name="payment_method" value="crypto" class="mr-2" onchange="togglePaymentMethod()">
                                <i class="ti ti-currency-bitcoin text-orange-500"></i>
                                <span class="ml-1">Cryptocurrency</span>
                            </label>
                        </div>
                        @error('payment_method')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Card Details (shown by default) -->
                    <div id="cardFields">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Card Number</label>
                            <input type="text" name="card_number" placeholder="1234 5678 9012 3456" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Expiry Date</label>
                                <input type="text" name="card_expiry" placeholder="MM/YY" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">CVC</label>
                                <input type="text" name="card_cvc" placeholder="123" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Crypto Details (hidden by default) -->
                    <div id="cryptoFields" style="display: none;">
                        <div class="bg-yellow-50 p-4 rounded-lg mb-4">
                            <p class="text-sm text-yellow-800 mb-2">
                                <i class="ti ti-info-circle"></i> Crypto Payment Instructions
                            </p>
                            <p class="text-xs text-yellow-700">Send the exact amount in USDT (TRC-20) to the wallet below:</p>
                            <p class="text-sm font-mono bg-white p-2 rounded mt-2 break-all">TXfkqwEk9qLHxuHmQJLPjBY6W2UyKfpYVa</p>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Transaction Hash (After sending)</label>
                            <input type="text" name="crypto_tx_hash" placeholder="Enter transaction hash" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition mt-4">
                        Pay ${{ number_format($payment->amount, 2) }}
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div>
            <div class="bg-gray-50 rounded-xl p-6 shadow-sm" style="border: 0.5px solid #e5e7eb;">
                <h3 class="font-semibold text-gray-900 mb-4">Order Summary</h3>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Service:</span>
                        <span class="font-medium">{{ $serviceRequest->service->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Request ID:</span>
                        <span>{{ $serviceRequest->reference_number }}</span>
                    </div>
                    <div class="border-t pt-3 mt-3">
                        <div class="flex justify-between">
                            <span class="font-semibold">Total:</span>
                            <span class="font-bold text-xl text-blue-900">${{ number_format($payment->amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-4 shadow-sm mt-4 text-center text-xs text-gray-500" style="border: 0.5px solid #e5e7eb;">
                <i class="ti ti-shield-lock"></i> Secure payment powered by Khadamati
            </div>
        </div>
        
    </div>
</div>

<script>
    function togglePaymentMethod() {
        const cardMethod = document.querySelector('input[value="card"]').checked;
        const cardFields = document.getElementById('cardFields');
        const cryptoFields = document.getElementById('cryptoFields');
        
        if (cardMethod) {
            cardFields.style.display = 'block';
            cryptoFields.style.display = 'none';
        } else {
            cardFields.style.display = 'none';
            cryptoFields.style.display = 'block';
        }
    }
    
    // Also make card fields optional based on selection
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        const isCard = document.querySelector('input[value="card"]').checked;
        const cardNumber = document.querySelector('input[name="card_number"]');
        const cardExpiry = document.querySelector('input[name="card_expiry"]');
        const cardCvc = document.querySelector('input[name="card_cvc"]');
        
        if (isCard) {
            if (!cardNumber.value || !cardExpiry.value || !cardCvc.value) {
                e.preventDefault();
                alert('Please fill in all card details');
            }
        }
    });
</script>
@endsection