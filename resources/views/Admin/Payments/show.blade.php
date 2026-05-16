@extends('layouts.app')

@section('content')

<div class="mb-6">

    <h1 class="text-2xl font-bold">
        Payment Details
    </h1>

</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- INFO --}}
    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow space-y-2">

        <p><strong>Reference:</strong> {{ $payment->request->reference_number }}</p>
        <p><strong>User:</strong> {{ $payment->user->name }}</p>
        <p><strong>Amount:</strong> {{ $payment->amount }} {{ $payment->currency }}</p>
        <p><strong>Method:</strong> {{ $payment->payment_method }}</p>

        <p><strong>Status:</strong> {{ $payment->status }}</p>

        @if($payment->paid_at)
            <p><strong>Paid At:</strong> {{ $payment->paid_at }}</p>
        @endif

    </div>

    {{-- UPDATE --}}
    <div class="bg-white p-6 rounded-xl shadow">

        <form method="POST"
              action="{{ route('admin.payments.update', $payment->id) }}">

            @csrf
            @method('PUT')

            <select name="status" class="w-full border rounded px-3 py-2 mb-3">
                <option value="pending" {{ $payment->status === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="paid" {{ $payment->status === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="failed" {{ $payment->status === 'failed' ? 'selected' : '' }}>Failed</option>
                <option value="refunded" {{ $payment->status === 'refunded' ? 'selected' : '' }}>Refunded</option>
            </select>

            <select name="payment_method" class="w-full border rounded px-3 py-2 mb-3">
                <option value="cash" {{ $payment->payment_method === 'cash' ? 'selected' : '' }}>Cash</option>
                <option value="card" {{ $payment->payment_method === 'card' ? 'selected' : '' }}>Card</option>
                <option value="crypto" {{ $payment->payment_method === 'crypto' ? 'selected' : '' }}>Crypto</option>
            </select>

            <textarea name="payment_details"
                      class="w-full border rounded px-3 py-2 mb-3"
                      placeholder="Notes..."></textarea>

            <button class="w-full bg-blue-600 text-white py-2 rounded">
                Update Payment
            </button>

        </form>

    </div>

</div>

@endsection