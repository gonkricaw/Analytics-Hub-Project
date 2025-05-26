<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of all notifications for management.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('notifications.view');

        $query = Notification::with(['creator', 'users' => function($query) {
            $query->selectRaw('count(*) as total_users')
                  ->selectRaw('sum(case when pivot_read_at is null then 1 else 0 end) as unread_users');
        }]);

        // Search by title or content
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'ILIKE', '%' . $searchTerm . '%')
                  ->orWhere('content', 'ILIKE', '%' . $searchTerm . '%');
            });
        }

        // Filter by creator
        if ($request->has('created_by') && !empty($request->created_by)) {
            $query->where('created_by_user_id', $request->created_by);
        }

        // Filter by date range
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSortFields = ['title', 'created_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $notifications = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'message' => 'Notifications retrieved successfully'
        ]);
    }

    /**
     * Store a newly created notification.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('notifications.create');

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'recipient_type' => ['required', Rule::in(['all', 'specific'])],
            'recipient_user_ids' => 'required_if:recipient_type,specific|array',
            'recipient_user_ids.*' => 'exists:idnbi_users,id',
        ]);

        $notification = Notification::create([
            'title' => $request->title,
            'content' => $request->content,
            'created_by_user_id' => $request->user()->id,
        ]);

        // Distribute to users based on recipient type
        if ($request->recipient_type === 'all') {
            $notification->distributeToAllUsers();
        } else {
            $notification->distributeToSpecificUsers($request->recipient_user_ids);
        }

        $notification->load(['creator', 'users']);

        return response()->json([
            'success' => true,
            'data' => $notification,
            'message' => 'Notification created and distributed successfully'
        ], 201);
    }

    /**
     * Display the specified notification.
     */
    public function show(Notification $notification): JsonResponse
    {
        $this->authorize('notifications.view');

        $notification->load(['creator', 'users' => function($query) {
            $query->select('idnbi_users.id', 'name', 'email')
                  ->addSelect('idnbi_user_notifications.read_at as read_at');
        }]);

        // Get statistics
        $stats = $notification->getStatistics();

        return response()->json([
            'success' => true,
            'data' => [
                'notification' => $notification,
                'statistics' => $stats,
            ],
            'message' => 'Notification retrieved successfully'
        ]);
    }

    /**
     * Update the specified notification.
     */
    public function update(Request $request, Notification $notification): JsonResponse
    {
        $this->authorize('notifications.update');

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $notification->update([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        $notification->load(['creator', 'users']);

        return response()->json([
            'success' => true,
            'data' => $notification,
            'message' => 'Notification updated successfully'
        ]);
    }

    /**
     * Remove the specified notification.
     */
    public function destroy(Notification $notification): JsonResponse
    {
        $this->authorize('notifications.delete');

        // Check if notification has any read status (to prevent data loss)
        $hasReadUsers = $notification->users()->wherePivot('read_at', '!=', null)->exists();
        
        if ($hasReadUsers) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete notification that has been read by users. This would cause data loss.',
            ], 422);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully'
        ]);
    }

    /**
     * Get notification statistics.
     */
    public function statistics(): JsonResponse
    {
        $this->authorize('notifications.view');

        $stats = [
            'total_notifications' => Notification::count(),
            'recent_notifications' => Notification::where('created_at', '>=', now()->subDays(7))->count(),
            'total_unread' => \DB::table('idnbi_user_notifications')
                ->whereNull('read_at')
                ->count(),
            'notifications_by_creator' => Notification::with('creator:id,name')
                ->selectRaw('created_by_user_id, count(*) as count')
                ->groupBy('created_by_user_id')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Notification statistics retrieved successfully'
        ]);
    }

    /**
     * Resend notification to users who haven't read it.
     */
    public function resend(Notification $notification): JsonResponse
    {
        $this->authorize('notifications.update');

        $unreadUserIds = $notification->users()
            ->wherePivot('read_at', null)
            ->pluck('idnbi_users.id')
            ->toArray();

        if (empty($unreadUserIds)) {
            return response()->json([
                'success' => false,
                'message' => 'All users have already read this notification.',
            ], 400);
        }

        // Create a new notification with the same content
        $newNotification = Notification::create([
            'title' => $notification->title . ' (Resent)',
            'content' => $notification->content,
            'created_by_user_id' => auth()->id(),
        ]);

        $newNotification->distributeToSpecificUsers($unreadUserIds);

        return response()->json([
            'success' => true,
            'data' => $newNotification,
            'message' => 'Notification resent to unread users successfully'
        ]);
    }
}
