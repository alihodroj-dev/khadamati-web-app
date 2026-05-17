<?php

namespace App\Http\View\Composers;

use App\Support\WebNotificationHelper;
use Illuminate\View\View;

class NavbarComposer
{
    // DEFERRED(roadmap): Live badge/dropdown updates (Echo/Reverb/SSE), not page reload.
    // Database notifications remain canonical. See docs/admin-office-roadmap.md#live-real-time-notifications

    public function __construct(
        private readonly WebNotificationHelper $webNotificationHelper,
    ) {}

    public function compose(View $view): void
    {
        $user = auth()->user();

        if ($user === null) {
            $view->with([
                'navNotifications' => collect(),
                'navUnreadCount' => 0,
            ]);

            return;
        }

        $notifications = $user->notifications()->latest()->take(8)->get();

        $view->with([
            'navNotifications' => $notifications->map(
                fn ($notification) => $this->webNotificationHelper->format($notification, $user)
            ),
            'navUnreadCount' => $user->unreadNotifications()->count(),
        ]);
    }
}
