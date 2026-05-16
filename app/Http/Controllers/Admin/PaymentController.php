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
        $payments = Payment::with(['request.service', 'user'])
            ->latest()
            ->paginate(10);

        return view('admin.payments.index', compact('payments'));
    }

    public function show($id)
    {
        $payment = Payment::with(['request.service', 'user'])
            ->findOrFail($id);

        return view('admin.payments.show', compact('payment'));
    }

    /**
     * Create payment for a service request
     */
    public function store(Request $request, $requestId)
    {
        $serviceRequest = ServiceRequest::findOrFail($requestId);

        $payment = Payment::create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => $serviceRequest->user_id,
            'amount' => $request->amount ?? $serviceRequest->service->base_fee,
            'currency' => $request->currency ?? 'USD',
            'payment_method' => $request->payment_method ?? 'cash',
            'status' => 'pending',
            'transaction_reference' => uniqid('PAY-'),
            'payment_details' => $request->payment_details,
        ]);

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment created successfully');
    }

    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $payment->update([
            'status' => $request->status,
            'payment_method' => $request->payment_method,
            'paid_at' => $request->status === 'paid' ? now() : null,
            'payment_details' => $request->payment_details,
        ]);

        return back()->with('success', 'Payment updated successfully');
    }
}
