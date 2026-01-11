<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Get all notifications for authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $query = $user->notifications();
        
        // Filter by read/unread status
        if ($request->has('read')) {
            $isRead = filter_var($request->read, FILTER_VALIDATE_BOOLEAN);
            $query = $isRead ? $query->read() : $query->unread();
        }
        
        // Filter by type
        if ($request->has('type')) {
            $query->where('data->type', $request->type);
        }
        
        // Search in notification data
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('data->title', 'like', "%{$search}%")
                  ->orWhere('data->body', 'like', "%{$search}%");
            });
        }
        
        // Order by
        $orderBy = $request->get('order_by', 'created_at');
        $orderDirection = $request->get('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDirection);
        
        // Paginate results
        $perPage = $request->get('per_page', 15);
        $notifications = $query->paginate($perPage);
        
        // Transform notifications for response
        $transformedNotifications = $notifications->map(function ($notification) {
            return $this->transformNotification($notification);
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $transformedNotifications,
                'unread_count' => $user->unreadNotifications()->count(),
                'total_count' => $user->notifications()->count(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ]
            ]
        ]);
    }

    /**
     * Get notification statistics
     */
    public function stats(): JsonResponse
    {
        $user = Auth::user();
        
        $allNotifications = $user->notifications();
        $unreadNotifications = $user->unreadNotifications();
        
        // Count by type
        $typeCounts = $allNotifications->get()
            ->groupBy('data.type')
            ->map(function ($notifications) {
                return [
                    'total' => $notifications->count(),
                    'unread' => $notifications->whereNull('read_at')->count()
                ];
            });
        
        // Recent notification types
        $recentTypes = $allNotifications->latest()
            ->take(5)
            ->pluck('data.type')
            ->unique()
            ->values();
        
        return response()->json([
            'success' => true,
            'data' => [
                'total' => $allNotifications->count(),
                'unread' => $unreadNotifications->count(),
                'read' => $allNotifications->count() - $unreadNotifications->count(),
                'by_type' => $typeCounts,
                'recent_types' => $recentTypes,
                'last_notification_at' => $allNotifications->latest()->first()?->created_at,
            ]
        ]);
    }

    /**
     * Get a single notification
     */
    public function show(string $id): JsonResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        
        // Mark as read when viewing
        if (!$notification->read_at) {
            $notification->markAsRead();
        }
        
        return response()->json([
            'success' => true,
            'data' => $this->transformNotification($notification, true)
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(string $id): JsonResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        
        if (!$notification->read_at) {
            $notification->markAsRead();
            
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
                'data' => $this->transformNotification($notification)
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Notification is already read'
        ], 400);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(string $id): JsonResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        
        if ($notification->read_at) {
            $notification->markAsUnread();
            
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as unread',
                'data' => $this->transformNotification($notification)
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Notification is already unread'
        ], 400);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        $unreadCount = $user->unreadNotifications()->count();
        
        if ($unreadCount > 0) {
            $user->unreadNotifications()->update(['read_at' => now()]);
            
            return response()->json([
                'success' => true,
                'message' => "{$unreadCount} notifications marked as read"
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'No unread notifications'
        ], 400);
    }

    /**
     * Delete a notification
     */
    public function destroy(string $id): JsonResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        
        $notification->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully'
        ]);
    }

    /**
     * Delete multiple notifications
     */
    public function destroyMultiple(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'required|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = Auth::user();
        $deletedCount = $user->notifications()
            ->whereIn('id', $request->ids)
            ->delete();
        
        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} notifications deleted successfully"
        ]);
    }

    /**
     * Clear all notifications
     */
    public function clearAll(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'sometimes|string|in:read,unread,all',
            'confirmation' => 'required|boolean|accepted'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        if (!$request->confirmation) {
            return response()->json([
                'success' => false,
                'message' => 'Please confirm you want to clear all notifications'
            ], 400);
        }
        
        $user = Auth::user();
        $type = $request->get('type', 'all');
        
        switch ($type) {
            case 'read':
                $count = $user->readNotifications()->count();
                $user->readNotifications()->delete();
                $message = "{$count} read notifications cleared";
                break;
                
            case 'unread':
                $count = $user->unreadNotifications()->count();
                $user->unreadNotifications()->delete();
                $message = "{$count} unread notifications cleared";
                break;
                
            case 'all':
            default:
                $count = $user->notifications()->count();
                $user->notifications()->delete();
                $message = "All {$count} notifications cleared";
                break;
        }
        
        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Get notifications by type
     */
    public function byType(string $type): JsonResponse
    {
        $user = Auth::user();
        
        $notifications = $user->notifications()
            ->where('data->type', $type)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        $transformedNotifications = $notifications->map(function ($notification) {
            return $this->transformNotification($notification);
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'type' => $type,
                'notifications' => $transformedNotifications,
                'total' => $notifications->total(),
                'unread' => $notifications->whereNull('read_at')->count()
            ]
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount(): JsonResponse
    {
        $user = Auth::user();
        $count = $user->unreadNotifications()->count();
        
        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count,
                'has_unread' => $count > 0
            ]
        ]);
    }

    /**
     * Get recent notifications (last 24 hours)
     */
    public function recent(): JsonResponse
    {
        $user = Auth::user();
        
        $recentNotifications = $user->notifications()
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        
        $transformedNotifications = $recentNotifications->map(function ($notification) {
            return $this->transformNotification($notification);
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $transformedNotifications,
                'total_recent' => $recentNotifications->count(),
                'unread_recent' => $recentNotifications->whereNull('read_at')->count()
            ]
        ]);
    }

    /**
     * Transform notification for API response
     */
    private function transformNotification(DatabaseNotification $notification, bool $detailed = false): array
    {
        $data = $notification->data;
        $baseData = [
            'id' => $notification->id,
            'type' => $data['type'] ?? 'unknown',
            'title' => $data['title'] ?? 'Notification',
            'body' => $data['body'] ?? '',
            'action_url' => $data['action_url'] ?? null,
            'action_label' => $data['action_label'] ?? 'View',
            'is_read' => !is_null($notification->read_at),
            'read_at' => $notification->read_at?->toISOString(),
            'created_at' => $notification->created_at->toISOString(),
            'created_at_human' => $notification->created_at->diffForHumans(),
            'icon' => $this->getIconForType($data['type'] ?? 'unknown'),
            'priority' => $data['priority'] ?? 'normal', // high, normal, low
        ];
        
        if ($detailed) {
            $baseData['data'] = $data;
            $baseData['channels'] = $data['channels'] ?? ['database'];
            $baseData['metadata'] = $data['metadata'] ?? [];
            $baseData['expires_at'] = $data['expires_at'] ?? null;
        }
        
        return $baseData;
    }

    /**
     * Get appropriate icon for notification type
     */
    private function getIconForType(string $type): string
    {
        $icons = [
            'order_confirmation' => 'shopping-bag',
            'order_shipped' => 'truck',
            'delivery_updates' => 'package',
            'out_of_stock_alerts' => 'alert-triangle',
            'weekly_discounts' => 'percent',
            'exclusive_member_offers' => 'crown',
            'seasonal_campaigns' => 'gift',
            'cart_reminders' => 'shopping-cart',
            'payment_billing' => 'credit-card',
            'system' => 'bell',
            'account' => 'user',
            'security' => 'shield',
        ];
        
        return $icons[$type] ?? 'bell';
    }
}