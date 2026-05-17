<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\ServiceRequest;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['serviceRequest.service', 'user'])
            ->latest()
            ->paginate(10);

        return view('admin.payments.index', compact('payments'));
    }

    public function show($id)
    {
        $payment = Payment::with(['serviceRequest.service', 'user'])
            ->findOrFail($id);

        return view('admin.payments.show', compact('payment'));
    }

    /**
     * Create payment for a service request
     */
    public function store(Request $request, $requestId)
    {
        $validated = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'payment_method' => ['nullable', 'in:card,cash,crypto'],
            'payment_details' => ['nullable', 'array'],
        ]);

        $serviceRequest = ServiceRequest::with('service')->findOrFail($requestId);

        $paymentMethod = $validated['payment_method'] ?? 'cash';

        $payment = Payment::create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => $serviceRequest->user_id,
            'amount' => $validated['amount'] ?? $serviceRequest->service->base_fee,
            'currency' => $validated['currency'] ?? 'USD',
            'payment_method' => $paymentMethod,
            'status' => 'pending',
            'transaction_reference' => uniqid('PAY-'),
            'payment_details' => $validated['payment_details']
                ?? $this->defaultPaymentDetails($paymentMethod),
        ]);

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment created successfully');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,paid,failed,refunded'],
            'payment_method' => ['nullable', 'in:card,cash,crypto'],
            'payment_details' => ['nullable', 'array'],
        ]);

        $payment = Payment::findOrFail($id);

        $paymentMethod = $validated['payment_method'] ?? $payment->payment_method;

        $payment->update([
            'status' => $validated['status'],
            'payment_method' => $paymentMethod,
            'paid_at' => $validated['status'] === 'paid' ? now() : null,
            'payment_details' => $validated['payment_details']
                ?? $payment->payment_details
                ?? $this->defaultPaymentDetails($paymentMethod),
        ]);

        return back()->with('success', 'Payment updated successfully');
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultPaymentDetails(string $paymentMethod): array
    {
        if ($paymentMethod === 'crypto') {
            return [
                'provider' => 'mock',
                'network' => 'testnet',
                'wallet_address' => '0x'.strtolower(uniqid('', true)),
            ];
        }

        return [
            'provider' => 'mock',
            'environment' => 'sandbox',
        ];
    }
}
