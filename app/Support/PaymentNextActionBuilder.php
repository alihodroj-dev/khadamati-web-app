<?php

namespace App\Support;

use App\Models\Payment;

class PaymentNextActionBuilder
{
    /**
     * @return array<string, mixed>|null
     */
    public static function for(Payment $payment): ?array
    {
        if ($payment->status !== 'pending') {
            return null;
        }

        return match ($payment->payment_method) {
            'card' => [
                'type'         => 'stripe_checkout',
                'checkout_url' => null, // call POST /payments/{id}/stripe/checkout to get the URL
                'endpoint'     => '/api/payments/'.$payment->id.'/stripe/checkout',
            ],
            'crypto' => self::cryptoTransferAction($payment),
            default => null,
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected static function cryptoTransferAction(Payment $payment): array
    {
        $details = $payment->payment_details ?? [];

        return [
            'type' => 'crypto_transfer',
            'network' => $details['network'] ?? 'testnet',
            'wallet_address' => $details['wallet_address'] ?? '',
            'expires_at' => $details['expires_at'] ?? null,
        ];
    }
}
