<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $citizen = User::query()->where('email', 'citizen.complete@khadamati.com')->first()
            ?? User::query()->where('email', 'citizen@khadamati.com')->first();

        $completedRequest = ServiceRequest::query()
            ->where('reference_number', 'like', '%SEED004')
            ->first();

        $underReviewRequest = ServiceRequest::query()
            ->where('reference_number', 'like', '%SEED001')
            ->first();

        if ($completedRequest && $citizen) {
            Payment::updateOrCreate(
                ['transaction_reference' => 'TXN-SEED-PAID-001'],
                [
                    'service_request_id' => $completedRequest->id,
                    'user_id' => $citizen->id,
                    'amount' => 0.00,
                    'currency' => 'USD',
                    'payment_method' => 'card',
                    'status' => 'paid',
                    'payment_details' => [
                        'card_brand' => 'visa',
                        'last4' => '4242',
                    ],
                    'paid_at' => now()->subDays(8),
                ]
            );
        }

        if ($underReviewRequest && $citizen) {
            Payment::updateOrCreate(
                ['transaction_reference' => 'TXN-SEED-PEND-001'],
                [
                    'service_request_id' => $underReviewRequest->id,
                    'user_id' => $citizen->id,
                    'amount' => 5.00,
                    'currency' => 'USD',
                    'payment_method' => 'crypto',
                    'status' => 'pending',
                    'payment_details' => [
                        'wallet_address' => '0xseed00000000000000000000000000000001',
                        'network' => 'ethereum-testnet',
                    ],
                ]
            );
        }
    }
}
