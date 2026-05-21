<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Notifications\PaymentUpdatedNotification;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeController extends Controller
{
    use ApiResponse;

    /**
     * Create a Stripe Checkout Session for mobile/web.
     *
     * Returns a hosted checkout_url the mobile app opens in a browser or WebView.
     * The user pays on Stripe's page — no Stripe SDK needed on the client.
     *
     * Optional body params:
     *   success_url — where Stripe redirects after successful payment (e.g. a deep link)
     *   cancel_url  — where Stripe redirects if the user cancels
     */
    public function createCheckout(Request $request, Payment $payment): JsonResponse
    {
        $this->authorize('process', $payment);

        if ($payment->payment_method !== 'card') {
            return $this->errorResponse(
                'Stripe payment is only available for card payments.',
                null,
                422
            );
        }

        if ($payment->status !== 'pending') {
            return $this->errorResponse(
                'Only pending payments can be processed.',
                null,
                422
            );
        }

        $validated = $request->validate([
            'success_url' => ['nullable', 'string'],
            'cancel_url'  => ['nullable', 'string'],
        ]);

        $appUrl = config('app.url');

        Stripe::setApiKey(config('services.stripe.secret'));

        $payment->load('serviceRequest.service');

        $session = CheckoutSession::create([
            'payment_method_types' => ['card'],
            'line_items'           => [[
                'price_data' => [
                    'currency'     => strtolower($payment->currency),
                    'product_data' => [
                        'name' => $payment->serviceRequest?->service?->name ?? 'Government Service',
                    ],
                    'unit_amount'  => (int) round($payment->amount * 100),
                ],
                'quantity' => 1,
            ]],
            'mode'        => 'payment',
            'success_url' => $validated['success_url'] ?? "{$appUrl}/payment/success?session_id={CHECKOUT_SESSION_ID}",
            'cancel_url'  => $validated['cancel_url']  ?? "{$appUrl}/payment/cancel",
            'metadata'    => [
                'payment_id'         => $payment->id,
                'service_request_id' => $payment->service_request_id,
                'user_id'            => $payment->user_id,
            ],
        ]);

        $payment->update([
            'transaction_reference' => $session->payment_intent ?? $session->id,
            'payment_details'       => array_merge($payment->payment_details ?? [], [
                'provider'                    => 'stripe',
                'environment'                 => 'sandbox',
                'stripe_checkout_session_id'  => $session->id,
                'stripe_payment_intent_id'    => $session->payment_intent,
            ]),
        ]);

        return $this->successResponse([
            'checkout_url' => $session->url,
            'session_id'   => $session->id,
        ], 'Stripe checkout session created.');
    }

    /**
     * Create a Stripe PaymentIntent (for clients using the Stripe mobile/web SDK).
     *
     * Returns client_secret + publishable_key.
     * The client confirms the payment using the Stripe SDK directly.
     */
    public function createIntent(Request $request, Payment $payment): JsonResponse
    {
        $this->authorize('process', $payment);

        if ($payment->payment_method !== 'card') {
            return $this->errorResponse(
                'Stripe payment is only available for card payments.',
                null,
                422
            );
        }

        if ($payment->status !== 'pending') {
            return $this->errorResponse(
                'Only pending payments can be processed.',
                null,
                422
            );
        }

        $existingIntentId = $payment->payment_details['stripe_payment_intent_id'] ?? null;

        Stripe::setApiKey(config('services.stripe.secret'));

        if ($existingIntentId) {
            $intent = PaymentIntent::retrieve($existingIntentId);
        } else {
            $intent = PaymentIntent::create([
                'amount'   => (int) round($payment->amount * 100),
                'currency' => strtolower($payment->currency),
                'metadata' => [
                    'payment_id'         => $payment->id,
                    'service_request_id' => $payment->service_request_id,
                    'user_id'            => $payment->user_id,
                ],
                'automatic_payment_methods' => ['enabled' => true],
            ]);

            $payment->update([
                'transaction_reference' => $intent->id,
                'payment_details'       => array_merge($payment->payment_details ?? [], [
                    'provider'                 => 'stripe',
                    'environment'              => 'sandbox',
                    'stripe_payment_intent_id' => $intent->id,
                ]),
            ]);
        }

        return $this->successResponse([
            'client_secret'     => $intent->client_secret,
            'publishable_key'   => config('services.stripe.key'),
            'payment_intent_id' => $intent->id,
        ], 'Stripe payment intent created.');
    }

    /**
     * Handle incoming Stripe webhook events.
     * Public endpoint — Stripe calls this after payment events.
     * Signature is verified to ensure the request came from Stripe.
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload   = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature.'], 400);
        }

        match ($event->type) {
            'payment_intent.succeeded'      => $this->handleIntentSucceeded($event->data->object),
            'payment_intent.payment_failed' => $this->handleIntentFailed($event->data->object),
            'checkout.session.completed'    => $this->handleCheckoutCompleted($event->data->object),
            default                         => null,
        };

        return response()->json(['received' => true]);
    }

    private function handleIntentSucceeded(object $intent): void
    {
        $payment = Payment::where('transaction_reference', $intent->id)->first();

        if (! $payment || $payment->status === 'paid') {
            return;
        }

        $payment->update([
            'status'  => 'paid',
            'paid_at' => now(),
            'payment_details' => array_merge($payment->payment_details ?? [], [
                'stripe_charge_id' => $intent->latest_charge ?? null,
                'succeeded_at'     => now()->toISOString(),
            ]),
        ]);

        $payment->load('user');
        $payment->user?->notify(new PaymentUpdatedNotification(
            $payment,
            'Payment successful',
            'Your payment has been processed successfully.'
        ));
    }

    private function handleIntentFailed(object $intent): void
    {
        $payment = Payment::where('transaction_reference', $intent->id)->first();

        if (! $payment || $payment->status !== 'pending') {
            return;
        }

        $payment->update([
            'status'          => 'failed',
            'payment_details' => array_merge($payment->payment_details ?? [], [
                'failure_reason' => $intent->last_payment_error?->message ?? 'Payment failed.',
                'failed_at'      => now()->toISOString(),
            ]),
        ]);

        $payment->load('user');
        $payment->user?->notify(new PaymentUpdatedNotification(
            $payment,
            'Payment failed',
            'Your payment could not be processed. Please try again.'
        ));
    }

    private function handleCheckoutCompleted(object $session): void
    {
        // Find by payment_intent (preferred) or by checkout session ID
        $payment = Payment::where('transaction_reference', $session->payment_intent)
            ->orWhere('transaction_reference', $session->id)
            ->first();

        if (! $payment || $payment->status === 'paid') {
            return;
        }

        $payment->update([
            'status'                => 'paid',
            'paid_at'               => now(),
            'transaction_reference' => $session->payment_intent ?? $session->id,
            'payment_details'       => array_merge($payment->payment_details ?? [], [
                'stripe_payment_intent' => $session->payment_intent,
                'succeeded_at'          => now()->toISOString(),
            ]),
        ]);

        $payment->load('user');
        $payment->user?->notify(new PaymentUpdatedNotification(
            $payment,
            'Payment successful',
            'Your payment has been processed successfully.'
        ));
    }
}
