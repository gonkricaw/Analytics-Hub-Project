<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserNotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get user's notifications with pagination and unread count.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $perPage = $request->get('per_page', 15);
        $onlyUnread = $request->boolean('only_unread');

        // Build query for user's notifications
        $query = $user->notifications()
            ->orderBy('idnbi_user_notifications.created_at', 'desc');

        // Filter by read status if requested
        if ($onlyUnread) {
            $query->whereNull('idnbi_user_notifications.read_at');
        }

        $notifications = $query->paginate($perPage);

        // Get unread count
        $unreadCount = $user->notifications()
            ->whereNull('idnbi_user_notifications.read_at')
            ->count();

        // Transform notifications for frontend
        $transformedNotifications = $notifications->getCollection()->map(function ($notification) {
            return [
                'id' => $notification->id,
                'title' => $notification->title,
                'content' => $notification->content,
                'isSeen' => !is_null($notification->pivot->read_at),
                'time' => $notification->pivot->created_at->diffForHumans(),
                'created_at' => $notification->pivot->created_at->toISOString(),
                'read_at' => $notification->pivot->read_at?->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $transformedNotifications,
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ],
                'unread_count' => $unreadCount,
            ],
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        $user = Auth::user();

        // Check if user has this notification
        $userNotification = $user->notifications()
            ->where('idnbi_notifications.id', $notification->id)
            ->first();

        if (!$userNotification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found for this user',
            ], 404);
        }

        // Mark as read if not already read
        if (is_null($userNotification->pivot->read_at)) {
            $user->notifications()
                ->updateExistingPivot($notification->id, [
                    'read_at' => now(),
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark a specific notification as unread.
     */
    public function markAsUnread(Request $request, Notification $notification): JsonResponse
    {
        $user = Auth::user();

        // Check if user has this notification
        $userNotification = $user->notifications()
            ->where('idnbi_notifications.id', $notification->id)
            ->first();

        if (!$userNotification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found for this user',
            ], 404);
        }

        // Mark as unread
        $user->notifications()
            ->updateExistingPivot($notification->id, [
                'read_at' => null,
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as unread',
        ]);
    }

    /**
     * Mark all user's notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Update all unread notifications for this user
        $updated = $user->notifications()
            ->whereNull('idnbi_user_notifications.read_at')
            ->update(['idnbi_user_notifications.read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => "Marked {$updated} notifications as read",
            'updated_count' => $updated,
        ]);
    }

    /**
     * Get unread notification count for user.
     */
    public function unreadCount(): JsonResponse
    {
        $user = Auth::user();
        
        $count = $user->notifications()
            ->whereNull('idnbi_user_notifications.read_at')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count,
            ],
        ]);
    }

    /**
     * Delete a specific notification for the user.
     */
    public function destroy(Request $request, Notification $notification): JsonResponse
    {
        $user = Auth::user();

        // Check if user has this notification
        $userNotification = $user->notifications()
            ->where('idnbi_notifications.id', $notification->id)
            ->first();

        if (!$userNotification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found for this user',
            ], 404);
        }

        // Remove the notification association for this user
        $user->notifications()->detach($notification->id);

        return response()->json([
            'success' => true,
            'message' => 'Notification removed',
        ]);
    }
}
