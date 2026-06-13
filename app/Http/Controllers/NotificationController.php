<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(20);
        return response()->json($notifications);
    }

    public function unread()
    {
        $notifications = Auth::user()->unreadNotifications()->limit(10)->get();
        $count = Auth::user()->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications,
            'count' => $count,
        ]);
    }

    public function markAsRead(string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }
}
