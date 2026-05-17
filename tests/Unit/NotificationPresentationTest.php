<?php

namespace Tests\Unit;

use App\Support\NotificationPresentation;
use Tests\TestCase;

class NotificationPresentationTest extends TestCase
{
    public function test_maps_request_update_to_service_request_deep_link(): void
    {
        $presentation = (new NotificationPresentation)->present([
            'type' => 'request_update',
            'title' => 'Request updated',
            'body' => 'Status changed',
            'service_request_id' => 123,
        ]);

        $this->assertSame('request_update', $presentation['type']);
        $this->assertSame('request', $presentation['icon']);
        $this->assertSame([
            'type' => 'service_request',
            'id' => 123,
        ], $presentation['deep_link']);
    }

    public function test_maps_document_upload_to_service_request_deep_link(): void
    {
        $presentation = (new NotificationPresentation)->present([
            'type' => 'document_upload',
            'service_request_id' => 55,
        ]);

        $this->assertSame('document', $presentation['icon']);
        $this->assertSame(['type' => 'service_request', 'id' => 55], $presentation['deep_link']);
    }

    public function test_maps_payment_and_appointment_updates(): void
    {
        $payment = (new NotificationPresentation)->present([
            'type' => 'payment_update',
            'payment_id' => 9,
        ]);

        $this->assertSame('payment', $payment['icon']);
        $this->assertSame(['type' => 'payment', 'id' => 9], $payment['deep_link']);

        $appointment = (new NotificationPresentation)->present([
            'type' => 'appointment_update',
            'appointment_id' => 4,
        ]);

        $this->assertSame('appointment', $appointment['icon']);
        $this->assertSame(['type' => 'appointment', 'id' => 4], $appointment['deep_link']);
    }
}
