<?php

namespace Tests\Feature;

use App\Http\Resources\PaymentReceiptResource;
use App\Models\Office;
use App\Models\Payment;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class PaymentReceiptTest extends TestCase
{
    use RefreshDatabase;

    public function test_receipt_includes_office_and_status_fields_for_owner(): void
    {
        $citizen = User::factory()->create([
            'role' => User::ROLE_CITIZEN,
            'name' => 'Jane Citizen',
            'national_id' => '123456789',
        ]);

        $payment = $this->createPaidPayment($citizen, 'completed');

        $response = $this->actingAs($citizen)->getJson("/api/payments/{$payment->id}/receipt");

        $response->assertOk()
            ->assertJsonPath('data.office_name', 'Beirut Office')
            ->assertJsonPath('data.service_request_status', 'completed')
            ->assertJsonPath('data.receipt_status', 'valid')
            ->assertJsonPath('data.citizen_national_id', '123456789')
            ->assertJsonPath('data.issued_at', '2026-05-16T12:00:00.000000Z');
    }

    public function test_receipt_unavailable_for_pending_payment(): void
    {
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $payment = $this->createPaidPayment($citizen, 'completed');
        $payment->update(['status' => 'pending', 'paid_at' => null]);

        $this->actingAs($citizen)->getJson("/api/payments/{$payment->id}/receipt")
            ->assertStatus(422);
    }

    public function test_receipt_omits_national_id_for_non_owner(): void
    {
        $owner = User::factory()->create([
            'role' => User::ROLE_CITIZEN,
            'national_id' => '123456789',
        ]);
        $other = User::factory()->create(['role' => User::ROLE_CITIZEN]);

        $payment = $this->createPaidPayment($owner, 'completed');

        $this->actingAs($other)->getJson("/api/payments/{$payment->id}/receipt")
            ->assertForbidden();

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $payment->load(['serviceRequest.service', 'serviceRequest.office', 'user']);

        $payload = (new PaymentReceiptResource($payment))
            ->toArray(Request::create('/api/payments/1/receipt', 'GET')->setUserResolver(fn () => $admin));

        $this->assertArrayNotHasKey('citizen_national_id', $payload);
    }

    private function createPaidPayment(User $citizen, string $requestStatus): Payment
    {
        $office = Office::query()->create([
            'name' => 'Beirut Office',
            'address' => 'Hamra',
            'is_active' => true,
        ]);

        $category = ServiceCategory::query()->create([
            'name' => 'Civil',
            'is_active' => true,
        ]);

        $service = Service::query()->create([
            'service_category_id' => $category->id,
            'office_id' => $office->id,
            'name' => 'Birth Certificate',
            'base_fee' => 5,
            'is_active' => true,
        ]);

        $serviceRequest = ServiceRequest::query()->create([
            'user_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'reference_number' => 'KHR-20260516-ABCDEF',
            'tracking_token' => ServiceRequest::generateTrackingToken(),
            'status' => $requestStatus,
        ]);

        return Payment::query()->create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => $citizen->id,
            'amount' => 5,
            'currency' => 'USD',
            'payment_method' => 'card',
            'status' => 'paid',
            'transaction_reference' => 'TXN-ABC123',
            'paid_at' => Carbon::parse('2026-05-16T12:00:00Z'),
        ]);
    }
}
