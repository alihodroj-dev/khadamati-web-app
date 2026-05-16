<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentReceiptResource;
use App\Http\Resources\PaymentResource;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Notifications\PaymentUpdatedNotification;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    use ApiResponse;

    private const STATUSES = ['pending', 'paid', 'failed', 'refunded'];

    private const PAYMENT_METHODS = ['card', 'cash', 'crypto'];

    public function index(Request $request)
    {
        $this->authorize('viewAny', Payment::class);

        $user = $request->user();
        $query = Payment::query()
            ->with(['serviceRequest', 'appointment', 'user'])
            ->latest();

        if ($user->isAdmin()) {
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
        } elseif ($user->isStaff()) {
            $query->whereHas('serviceRequest', function ($q) use ($user) {
                $q->where('assigned_staff_id', $user->id);
            });
        } else {
            $validated = $request->validate([
                'status' => ['nullable', 'string', Rule::in(self::STATUSES)],
                'payment_method' => ['nullable', 'string', Rule::in(self::PAYMENT_METHODS)],
                'from_date' => ['nullable', 'date', 'date_format:Y-m-d'],
                'to_date' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            ]);

            $query->where('user_id', $user->id);

            if (! empty($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            if (! empty($validated['payment_method'])) {
                $query->where('payment_method', $validated['payment_method']);
            }

            if (! empty($validated['from_date'])) {
                $query->whereDate('created_at', '>=', $validated['from_date']);
            }

            if (! empty($validated['to_date'])) {
                $query->whereDate('created_at', '<=', $validated['to_date']);
            }
        }

        $payments = $query->get();

        return $this->successResponse(
            PaymentResource::collection($payments),
            'Payments retrieved successfully.'
        );
    }

    public function store(Request $request)
    {
        $this->authorize('create', Payment::class);

        $validated = $request->validate([
            'service_request_id' => ['required', 'exists:service_requests,id'],
            'appointment_id' => ['nullable', 'exists:appointments,id'],
            'payment_method' => ['required', 'in:card,cash,crypto'],
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        $serviceRequest = ServiceRequest::with('service')
            ->findOrFail($validated['service_request_id']);

        if ($serviceRequest->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized service request.', null, 403);
        }

        if (! $serviceRequest->service) {
            return $this->errorResponse(
                'Service not found for this service request.',
                null,
                404
            );
        }

        $amount = $validated['amount'] ?? $serviceRequest->service->base_fee;

        if ((float) $amount <= 0) {
            return $this->errorResponse(
                'This service does not require payment.',
                null,
                422
            );
        }

        if (isset($validated['appointment_id'])) {
            $appointment = Appointment::findOrFail($validated['appointment_id']);

            if (
                $appointment->service_request_id !== $serviceRequest->id ||
                $appointment->user_id !== $request->user()->id
            ) {
                return $this->errorResponse('Unauthorized appointment.', null, 403);
            }
        }

        $existing = Payment::where('service_request_id', $serviceRequest->id)
            ->whereIn('status', ['pending', 'paid'])
            ->first();

        if ($existing) {
            return $this->errorResponse(
                'A payment already exists for this service request.',
                null,
                422
            );
        }

        $payment = Payment::create([
            'service_request_id' => $validated['service_request_id'],
            'appointment_id' => $validated['appointment_id'] ?? null,
            'user_id' => $request->user()->id,
            'amount' => $amount,
            'currency' => strtoupper($validated['currency'] ?? 'USD'),
            'payment_method' => $validated['payment_method'],
            'status' => 'pending',
            'transaction_reference' => 'TXN-'.strtoupper(Str::random(12)),
            'payment_details' => $this->buildInitialPaymentDetails($validated['payment_method']),
        ]);

        $payment->load(['serviceRequest', 'appointment']);

        $payment->user?->notify(new PaymentUpdatedNotification(
            $payment,
            'Payment initiated',
            'Your payment has been initiated.'
        ));

        return $this->successResponse(
            new PaymentResource($payment),
            'Payment initiated successfully.',
            201
        );
    }

    public function show(Payment $payment)
    {
        $this->authorize('view', $payment);

        $payment->load(['serviceRequest', 'appointment', 'user']);

        return $this->successResponse(
            new PaymentResource($payment),
            'Payment retrieved successfully.'
        );
    }

    public function receipt(Payment $payment)
    {
        $this->authorize('receipt', $payment);

        if ($payment->status !== 'paid') {
            return $this->errorResponse(
                'Receipt is only available for paid payments.',
                null,
                422
            );
        }

        $payment->load(['serviceRequest.service', 'user']);

        return $this->successResponse(
            new PaymentReceiptResource($payment),
            'Payment receipt retrieved successfully.'
        );
    }

    public function process(Request $request, Payment $payment)
    {
        $this->authorize('process', $payment);

        if ($payment->status !== 'pending') {
            return $this->errorResponse(
                'Only pending payments can be processed.',
                null,
                422
            );
        }

        $validated = $request->validate([
            'mock_status' => ['nullable', 'in:paid,failed'],
            'mock_message' => ['nullable', 'string', 'max:255'],
        ]);

        $status = $validated['mock_status'] ?? 'paid';

        $payment->update([
            'status' => $status,
            'paid_at' => $status === 'paid' ? now() : null,
            'payment_details' => array_merge($payment->payment_details ?? [], [
                'mock_status' => $status,
                'mock_message' => $validated['mock_message'] ?? null,
                'processed_at' => now()->toISOString(),
            ]),
        ]);

        $payment->load(['serviceRequest', 'appointment']);

        return $this->successResponse(
            new PaymentResource($payment),
            $status === 'paid' ? 'Payment completed successfully.' : 'Payment failed.'
        );
    }

    public function markAsPaid(Payment $payment)
    {
        $this->authorize('update', $payment);

        if ($payment->status !== 'pending') {
            return $this->errorResponse(
                'Only pending payments can be marked as paid.',
                null,
                422
            );
        }

        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $payment->load(['serviceRequest', 'appointment', 'user']);

        $payment->user?->notify(new PaymentUpdatedNotification(
            $payment,
            'Payment marked as paid',
            'Your payment has been marked as paid.'
        ));

        return $this->successResponse(
            new PaymentResource($payment),
            'Payment marked as paid.'
        );
    }

    public function refund(Payment $payment)
    {
        $this->authorize('update', $payment);

        if ($payment->status !== 'paid') {
            return $this->errorResponse(
                'Only paid payments can be refunded.',
                null,
                422
            );
        }

        $payment->update(['status' => 'refunded']);

        $payment->load(['serviceRequest', 'appointment', 'user']);

        $payment->user?->notify(new PaymentUpdatedNotification(
            $payment,
            'Payment refunded',
            'Your payment has been refunded.'
        ));

        return $this->successResponse(
            new PaymentResource($payment),
            'Payment refunded successfully.'
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildInitialPaymentDetails(string $paymentMethod): array
    {
        if ($paymentMethod === 'crypto') {
            return [
                'provider' => 'mock',
                'network' => 'testnet',
                'wallet_address' => '0x'.strtolower(Str::random(40)),
                'expires_at' => now()->addHours(24)->toISOString(),
            ];
        }

        if ($paymentMethod === 'card') {
            return [
                'provider' => 'mock',
                'environment' => 'sandbox',
            ];
        }

        return [
            'provider' => 'mock',
        ];
    }
}
