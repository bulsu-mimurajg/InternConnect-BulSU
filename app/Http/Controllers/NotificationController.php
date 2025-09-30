<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{

    /**
     * Get unread notifications count (for AJAX)
     */
    public function getUnreadCount(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $count = Notification::getUnreadCount($user->id);

        return response()->json(['count' => $count]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Notification $notification): \Illuminate\Http\JsonResponse
    {
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        Notification::markAllAsRead($user->id);

        return response()->json(['success' => true]);
    }

    /**
     * Mark a notification as unread
     */
    public function markAsUnread(Notification $notification): \Illuminate\Http\JsonResponse
    {
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->update(['is_read' => false]);

        return response()->json(['success' => true]);
    }

    /**
     * Get notifications for AJAX (for dropdown)
     */
    public function getNotifications(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $notifications = Notification::getForUser($user->id, 10);

        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => Notification::getUnreadCount($user->id),
        ]);
    }
}