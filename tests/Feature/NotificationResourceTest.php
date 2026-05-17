<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\AppointmentUpdatedNotification;
use App\Notifications\PaymentUpdatedNotification;
use App\Notifications\RequestUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_notifications_returns_ios_friendly_payload(): void
    {
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $serviceRequest = $this->createServiceRequest($citizen);

        $citizen->notify(new RequestUpdatedNotification(
            $serviceRequest,
            'Request updated',
            'Your request is under review.'
        ));

        $payment = Payment::query()->create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => $citizen->id,
            'amount' => 10,
            'currency' => 'USD',
            'payment_method' => 'card',
            'status' => 'paid',
            'transaction_reference' => 'TXN-1',
            'paid_at' => now(),
        ]);

        $citizen->notify(new PaymentUpdatedNotification(
            $payment,
            'Payment received',
            'Your payment was successful.'
        ));

        $appointment = Appointment::query()->create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => $citizen->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '09:00',
            'status' => 'scheduled',
        ]);

        $citizen->notify(new AppointmentUpdatedNotification(
            $appointment,
            'Appointment booked',
            'Your appointment has been booked.'
        ));

        $response = $this->actingAs($citizen)->getJson('/api/notifications');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'type',
                        'title',
                        'body',
                        'icon',
                        'deep_link',
                        'read_at',
                        'created_at',
                    ],
                ],
            ])
            ->assertJsonFragment([
                'type' => 'request_update',
                'icon' => 'request',
                'deep_link' => [
                    'type' => 'service_request',
                    'id' => $serviceRequest->id,
                ],
            ])
            ->assertJsonFragment([
                'type' => 'payment_update',
                'icon' => 'payment',
                'deep_link' => [
                    'type' => 'payment',
                    'id' => $payment->id,
                ],
            ])
            ->assertJsonFragment([
                'type' => 'appointment_update',
                'icon' => 'appointment',
                'deep_link' => [
                    'type' => 'appointment',
                    'id' => $appointment->id,
                ],
            ]);

        $this->assertArrayNotHasKey('data', $response->json('data.0'));
    }

    private function createServiceRequest(User $citizen): ServiceRequest
    {
        $category = ServiceCategory::query()->create([
            'name' => 'Civil',
            'is_active' => true,
        ]);

        $service = Service::query()->create([
            'service_category_id' => $category->id,
            'name' => 'Passport',
            'base_fee' => 10,
            'is_active' => true,
        ]);

        return ServiceRequest::query()->create([
            'user_id' => $citizen->id,
            'service_id' => $service->id,
            'reference_number' => 'KHR-TEST-001',
            'tracking_token' => ServiceRequest::generateTrackingToken(),
            'status' => 'under_review',
        ]);
    }
}
