<?php

namespace Tests\Unit;

use App\Models\User;
use App\Support\WebNotificationHelper;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

class WebNotificationHelperTest extends TestCase
{
    public function test_formats_notification_payload_for_display(): void
    {
        $notification = new DatabaseNotification([
            'id' => '550e8400-e29b-41d4-a716-446655440000',
            'data' => [
                'type' => 'request_update',
                'title' => 'Hello',
                'body' => 'World',
                'service_request_id' => 42,
            ],
            'read_at' => null,
            'created_at' => now()->subMinutes(5),
        ]);

        $user = new User(['role' => User::ROLE_ADMIN]);

        $formatted = (new WebNotificationHelper)->format($notification, $user);

        $this->assertSame('Hello', $formatted['title']);
        $this->assertSame('World', $formatted['body']);
        $this->assertSame('ti-clipboard-list', $formatted['icon']);
        $this->assertTrue($formatted['is_unread']);
        $this->assertStringContainsString('/admin/requests/42', $formatted['url'] ?? '');
    }
}
