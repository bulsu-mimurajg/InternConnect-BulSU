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
    public function getNotifications(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        
        // Get pagination parameters
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10);
        $filter = $request->get('filter', 'all');
        $showRead = $request->get('show_read', true);
        
        // Build query
        $query = Notification::where('user_id', $user->id);
        
        // Apply filters
        if (!$showRead) {
            $query->where('is_read', false);
        }
        
        if ($filter !== 'all') {
            switch ($filter) {
                case 'endorsement':
                    $query->where('type', 'hte_endorsement');
                    break;
                case 'deadline':
                    $query->whereIn('type', ['hte_deadline', 'student_deadline', 'unified_deadline', 'deadline_released', 'deadline_expired']);
                    break;
                case 'placement':
                    $query->whereIn('type', ['student_placement', 'student_placement_status']);
                    break;
                case 'approval':
                    $query->whereIn('type', [
                        'student_approval_request', 
                        'student_status_change', 
                        'student_registration_pending',
                        'new_student_registration',
                        'student_verification_pending',
                        'student_approved',
                        'student_approval_needed'
                    ]);
                    break;
            }
        }
        
        // Get total count for pagination
        $totalCount = $query->count();
        
        // Get paginated notifications
        $notifications = $query->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => Notification::getUnreadCount($user->id),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalCount,
                'last_page' => ceil($totalCount / $perPage),
                'from' => $totalCount > 0 ? (($page - 1) * $perPage) + 1 : 0,
                'to' => min($page * $perPage, $totalCount),
            ],
        ])->header('Content-Type', 'application/json');
    }
}