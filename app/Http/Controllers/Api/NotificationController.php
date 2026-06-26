<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // ── GET /api/notifications ────────────────────────────────────────────────
    /**
     * List notifications for the authenticated user (paginated, newest first).
     * ?filter=unread to get only unread.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AppNotification::forUser($request->user()->id)
            ->orderByDesc('created_at');

        if ($request->query('filter') === 'unread') {
            $query->unread();
        }

        $notifications = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $notifications->items(),
            'meta'    => [
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'total'        => $notifications->total(),
            ],
        ]);
    }

    // ── GET /api/notifications/unread-count ──────────────────────────────────
    /**
     * Return the unread notification count for the authenticated user.
     * Used for badge counters in the app.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = AppNotification::forUser($request->user()->id)
            ->unread()
            ->count();

        return response()->json([
            'success' => true,
            'data'    => ['count' => $count],
        ]);
    }

    // ── PUT /api/notifications/{id}/read ─────────────────────────────────────
    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $notification = AppNotification::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
        ]);
    }

    // ── PUT /api/notifications/read-all ──────────────────────────────────────
    /**
     * Mark ALL unread notifications as read for the authenticated user.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $updated = AppNotification::forUser($request->user()->id)
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => "{$updated} notifications marked as read.",
        ]);
    }

    // ── POST /api/notifications/announcement ─────────────────────────────────
    /**
     * Send a system-wide announcement push + in-app notification to all users.
     * Accessible only by admin.
     */
    public function announcement(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body'  => 'required|string',
        ]);

        $users = User::all()->filter(function ($user) {
            $prefs = $user->notification_preferences ?? [];
            return $prefs['system_updates'] ?? true;
        });

        $tokens = $users->pluck('fcm_token')->filter()->toArray();

        $notifService = app(NotificationService::class);

        // Push to all
        if (!empty($tokens)) {
            $notifService->sendMulticast(
                $tokens,
                $validated['title'],
                $validated['body'],
                ['type' => 'system_announcement']
            );
        }

        // In-app for all users (including those without FCM token)
        $now = now();
        $inserts = [];
        
        foreach ($users as $user) {
            $inserts[] = [
                'user_id'    => $user->id,
                'type'       => 'system_announcement',
                'title'      => $validated['title'],
                'body'       => $validated['body'],
                'data'       => json_encode([]),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Chunk inserts to avoid memory exhaustion / long query times
            if (count($inserts) >= 500) {
                AppNotification::insert($inserts);
                $inserts = [];
            }
        }
        
        if (!empty($inserts)) {
            AppNotification::insert($inserts);
        }

        return response()->json([
            'success' => true,
            'message' => 'Announcement sent to ' . count($tokens) . ' devices and ' . $allUserIds->count() . ' in-app inboxes.',
        ]);
    }
}
