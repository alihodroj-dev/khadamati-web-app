<?php

namespace App\Support;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class WebNotificationHelper
{
    public function __construct(
        private readonly NotificationPresentation $presentation = new NotificationPresentation,
    ) {}

    /**
     * @return array{
     *     id: string,
     *     title: string,
     *     body: string,
     *     icon: string,
     *     url: null|string,
     *     is_unread: bool,
     *     created_at: \Illuminate\Support\Carbon|null,
     *     created_at_human: string
     * }
     */
    public function format(DatabaseNotification $notification, ?User $user = null): array
    {
        $user ??= auth()->user();
        $data = is_array($notification->data) ? $notification->data : [];
        $presented = $this->presentation->present($data);

        return [
            'id' => $notification->id,
            'title' => (string) ($data['title'] ?? 'Notification'),
            'body' => (string) ($data['body'] ?? ''),
            'icon' => $this->tablerIcon($presented['icon']),
            'url' => $user ? $this->resolveUrl($user, $presented, $data) : null,
            'is_unread' => $notification->read_at === null,
            'created_at' => $notification->created_at,
            'created_at_human' => $notification->created_at?->diffForHumans() ?? '',
        ];
    }

    public function tablerIcon(string $icon): string
    {
        return match ($icon) {
            'request' => 'ti-clipboard-list',
            'payment' => 'ti-credit-card',
            'appointment' => 'ti-calendar',
            'document' => 'ti-file-upload',
            default => 'ti-bell',
        };
    }

    /**
     * @param  array{type: string, icon: string, deep_link: null|array{type: string, id: int}}  $presented
     * @param  array<string, mixed>  $data
     */
    public function resolveUrl(User $user, array $presented, array $data): ?string
    {
        $deepLink = $presented['deep_link'];

        if ($deepLink === null) {
            return null;
        }

        return match ($deepLink['type']) {
            'service_request' => $this->serviceRequestUrl($user, $deepLink['id']),
            'appointment' => $this->appointmentUrl($user, $deepLink['id']),
            'payment' => $this->paymentUrl($user, $deepLink['id']),
            default => null,
        };
    }

    private function serviceRequestUrl(User $user, int $id): string
    {
        if ($user->isAdmin()) {
            return route('admin.requests.show', $id);
        }

        return route('staff.requests.show', $id);
    }

    private function appointmentUrl(User $user, int $id): string
    {
        if ($user->isAdmin()) {
            return route('admin.appointments.show', $id);
        }

        return route('staff.appointments.show', $id);
    }

    private function paymentUrl(User $user, int $id): ?string
    {
        if ($user->isAdmin()) {
            return route('admin.payments.show', $id);
        }

        $serviceRequestId = Payment::query()
            ->whereKey($id)
            ->value('service_request_id');

        if ($serviceRequestId !== null) {
            return route('staff.requests.show', $serviceRequestId);
        }

        return route('staff.dashboard');
    }
}
