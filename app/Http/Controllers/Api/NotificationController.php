<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = $request->user()
            ->notifications()
            ->latest();

        if ($request->boolean('unread_only')) {
            $query->whereNull('read_at');
        }

        return $this->successResponse(
            NotificationResource::collection($query->get()),
            'Notifications retrieved successfully.'
        );
    }

    public function markAsRead(Request $request, string $notification)
    {
        $item = $request->user()
            ->notifications()
            ->where('id', $notification)
            ->firstOrFail();

        $item->markAsRead();

        return $this->successResponse(
            new NotificationResource($item),
            'Notification marked as read.'
        );
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return $this->successResponse(null, 'Notifications marked as read.');
    }
}
