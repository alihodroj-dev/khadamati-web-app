<?php

namespace Tests\Unit;

use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Support\PaymentNextActionBuilder;
use Tests\TestCase;

class PaymentNextActionBuilderTest extends TestCase
{
    public function test_card_pending_payment_returns_mock_card_confirmation(): void
    {
        $payment = new Payment([
            'payment_method' => 'card',
            'status' => 'pending',
            'payment_details' => ['provider' => 'mock', 'environment' => 'sandbox'],
        ]);
        $payment->id = 42;

        $action = PaymentNextActionBuilder::for($payment);

        $this->assertSame('mock_card_confirmation', $action['type']);
        $this->assertSame('Use /payments/42/process in sandbox', $action['message']);
    }

    public function test_crypto_pending_payment_returns_crypto_transfer(): void
    {
        $payment = new Payment([
            'payment_method' => 'crypto',
            'status' => 'pending',
            'payment_details' => [
                'network' => 'testnet',
                'wallet_address' => '0xabc123',
                'expires_at' => '2026-05-18T12:00:00.000000Z',
            ],
        ]);

        $action = PaymentNextActionBuilder::for($payment);

        $this->assertSame('crypto_transfer', $action['type']);
        $this->assertSame('testnet', $action['network']);
        $this->assertSame('0xabc123', $action['wallet_address']);
        $this->assertSame('2026-05-18T12:00:00.000000Z', $action['expires_at']);
    }

    public function test_paid_payment_has_no_next_action(): void
    {
        $payment = new Payment([
            'payment_method' => 'card',
            'status' => 'paid',
        ]);
        $payment->id = 44;

        $this->assertNull(PaymentNextActionBuilder::for($payment));
    }

    public function test_payment_resource_includes_next_action(): void
    {
        $payment = new Payment([
            'payment_method' => 'card',
            'status' => 'pending',
            'amount' => 25,
            'currency' => 'USD',
            'payment_details' => [],
        ]);
        $payment->id = 42;

        $payload = (new PaymentResource($payment))->resolve();

        $this->assertSame('mock_card_confirmation', $payload['next_action']['type']);
    }
}
