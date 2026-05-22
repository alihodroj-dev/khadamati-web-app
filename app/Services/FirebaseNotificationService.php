<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Throwable;

class FirebaseNotificationService
{
    public function __construct(
        private readonly Messaging $messaging,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): bool
    {
        if (! $user->push_notifications_enabled || blank($user->fcm_token)) {
            return false;
        }

        return $this->sendToToken($user->fcm_token, $title, $body, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        if (blank($token)) {
            return false;
        }

        $apnsConfig = ApnsConfig::new()
            ->withImmediatePriority()
            ->withDefaultSound()
            ->withApsField('alert', [
                'title' => $title,
                'body' => $body,
            ]);

        $message = CloudMessage::new()
            ->withToken($token)
            ->withNotification(Notification::create($title, $body))
            ->withApnsConfig($apnsConfig)
            ->withData($this->stringifyData($data));

        try {
            $this->messaging->send($message);

            return true;
        } catch (MessagingException|FirebaseException|Throwable $exception) {
            Log::warning('FCM push notification failed.', [
                'message' => $exception->getMessage(),
                'token_preview' => substr($token, 0, 12),
            ]);

            return false;
        }
    }

    /**
     * FCM data payload values must be strings.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    private function stringifyData(array $data): array
    {
        $payload = [];

        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }

            $payload[(string) $key] = is_scalar($value)
                ? (string) $value
                : json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return $payload;
    }
}
