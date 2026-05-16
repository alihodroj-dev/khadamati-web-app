<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        // TODO: replace with real notifications when friend sets up the table
        // $notifications = auth()->user()->notifications()->latest()->take(10)->get();
        $notifications = collect([]);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        // TODO: auth()->user()->notifications()->findOrFail($id)->markAsRead();
        return redirect()->route('notifications.index')
        ->with('success', 'All notifications marked as read');
    }

    public function markAllRead()
    {
        // TODO: auth()->user()->unreadNotifications->markAsRead();
        return redirect()->back()
        ->with('success', 'Notification marked as read');
    }
}