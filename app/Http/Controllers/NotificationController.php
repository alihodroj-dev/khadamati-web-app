<?php

namespace App\Http\Controllers;

use App\Support\WebNotificationHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        private readonly WebNotificationHelper $webNotificationHelper,
    ) {}

    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        $formatted = $notifications->getCollection()->map(
            fn ($notification) => $this->webNotificationHelper->format($notification, $request->user())
        );

        $notifications->setCollection($formatted);

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        $formatted = $this->webNotificationHelper->format($notification, $request->user());

        if ($formatted['url'] !== null) {
            return redirect($formatted['url'])
                ->with('success', 'Notification marked as read.');
        }

        return redirect()
            ->route('notifications.index')
            ->with('success', 'Notification marked as read.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return redirect()
            ->back()
            ->with('success', 'All notifications marked as read.');
    }
}
