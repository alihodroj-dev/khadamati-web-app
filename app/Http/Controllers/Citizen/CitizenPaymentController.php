<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Payment;
use Illuminate\Http\Request;

class CitizenPaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::where('user_id', auth()->id())
            ->with('serviceRequest')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('citizen.payments.index', compact('payments'));
    }

    public function show($id)
    {
        $payment = Payment::where('user_id', auth()->id())
            ->with('serviceRequest')
            ->findOrFail($id);

        return view('citizen.payments.show', compact('payment'));
    }

    public function checkout($requestId)
    {
        $serviceRequest = ServiceRequest::where('user_id', auth()->id())
            ->with('service')
            ->findOrFail($requestId);

        // Check if payment already exists
        $payment = Payment::where('service_request_id', $serviceRequest->id)->first();

        if (!$payment) {
            $payment = Payment::create([
                'service_request_id' => $serviceRequest->id,
                'user_id' => auth()->id(),
                'amount' => $serviceRequest->service->base_fee,
                'currency' => 'USD',
                'payment_method' => 'card',
                'status' => 'pending',
                'transaction_reference' => 'PAY-' . uniqid(),
            ]);
        }

        return view('citizen.payments.checkout', compact('payment', 'serviceRequest'));
    }

    public function process(Request $request, $paymentId)
    {
        $payment = Payment::where('user_id', auth()->id())->findOrFail($paymentId);

        $request->validate([
            'payment_method' => ['required', 'in:card,crypto'],
            'card_number' => ['required_if:payment_method,card', 'nullable', 'string'],
            'card_expiry' => ['required_if:payment_method,card', 'nullable', 'string'],
            'card_cvc' => ['required_if:payment_method,card', 'nullable', 'string'],
        ]);

        // Simulate payment processing
        // In production, integrate with Stripe, Coinbase, etc.

        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => $request->payment_method,
            'payment_details' => [
                'simulated' => true,
                'method' => $request->payment_method,
                'processed_at' => now()->toISOString(),
            ],
        ]);

        // Update service request status if needed
        $payment->serviceRequest->update(['status' => 'under_review']);

        return redirect()->route('citizen.payments.show', $payment->id)
            ->with('success', 'Payment completed successfully!');
    }
}