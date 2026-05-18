<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\FirebaseNotificationService;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Mockery;
use Tests\TestCase;

class FirebaseNotificationServiceTest extends TestCase
{
    public function test_it_sends_push_notification_to_user_with_fcm_token(): void
    {
        $messaging = Mockery::mock(Messaging::class);
        $messaging
            ->shouldReceive('send')
            ->once()
            ->with(Mockery::type(CloudMessage::class))
            ->andReturn(['name' => 'message-id']);

        $user = new User([
            'fcm_token' => 'fcm-token',
            'push_notifications_enabled' => true,
        ]);

        $sent = (new FirebaseNotificationService($messaging))->sendToUser(
            $user,
            'Request updated',
            'Your request status changed.',
            ['type' => 'request_status_update', 'request_id' => 123]
        );

        $this->assertTrue($sent);
    }

    public function test_it_skips_users_without_fcm_token(): void
    {
        $messaging = Mockery::mock(Messaging::class);
        $messaging->shouldNotReceive('send');

        $user = new User([
            'push_notifications_enabled' => true,
        ]);

        $sent = (new FirebaseNotificationService($messaging))->sendToUser(
            $user,
            'Title',
            'Body'
        );

        $this->assertFalse($sent);
    }

    public function test_it_skips_users_with_push_notifications_disabled(): void
    {
        $messaging = Mockery::mock(Messaging::class);
        $messaging->shouldNotReceive('send');

        $user = new User([
            'fcm_token' => 'fcm-token',
            'push_notifications_enabled' => false,
        ]);

        $sent = (new FirebaseNotificationService($messaging))->sendToUser(
            $user,
            'Title',
            'Body'
        );

        $this->assertFalse($sent);
    }
}
