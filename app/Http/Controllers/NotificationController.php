<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    /**
     * Get notifications for the authenticated user
     */
    public function index(): Response
    {
        $user = Auth::user();
        $notifications = Notification::getForUser($user->id, 20);
        $unreadCount = Notification::getUnreadCount($user->id);

        return Inertia::render('notifications/index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

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