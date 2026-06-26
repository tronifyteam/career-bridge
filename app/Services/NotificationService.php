<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationService
{
    // ── Push via FCM ─────────────────────────────────────────────────────────

    /**
     * Send a push notification to a specific FCM token.
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        try {
            $messaging = app(Messaging::class);
            $notification = Notification::create($title, $body);

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification)
                ->withData($data);

            $messaging->send($message);

            Log::info("FCM Notification sent to token: {$token}");
            return true;
        } catch (Throwable $e) {
            Log::error("Failed to send FCM Notification to token: {$token}. Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a push notification to multiple FCM tokens.
     */
    public function sendMulticast(array $tokens, string $title, string $body, array $data = []): bool
    {
        $tokens = array_filter($tokens);
        if (empty($tokens)) {
            return false;
        }

        try {
            $messaging = app(Messaging::class);
            $notification = Notification::create($title, $body);

            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData($data);

            $messaging->sendMulticast($message, $tokens);

            Log::info("FCM Multicast Notification sent to " . count($tokens) . " tokens.");
            return true;
        } catch (Throwable $e) {
            Log::error("Failed to send FCM Multicast. Error: " . $e->getMessage());
            return false;
        }
    }

    // ── In-App Notification ───────────────────────────────────────────────────

    /**
     * Create an in-app notification row for a user.
     */
    public function createInApp(int $userId, string $type, string $title, string $body, array $data = []): AppNotification
    {
        return AppNotification::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'body'    => $body,
            'data'    => $data,
        ]);
    }

    // ── Combined: Push + In-App ───────────────────────────────────────────────

    /**
     * Send a push notification AND create an in-app notification for a single user.
     * Pass a User model; if they have no FCM token, only in-app is created.
     *
     * @param User   $recipient
     * @param string $type      Notification type key (e.g. 'chat_message')
     * @param string $title
     * @param string $body
     * @param array  $data      Extra payload merged into push data and stored in DB
     */
    public function notify(User $recipient, string $type, string $title, string $body, array $data = []): void
    {
        // Check preferences
        $prefs = $recipient->notification_preferences ?? [
            'job_alerts' => true,
            'chat_messages' => true,
            'system_updates' => true,
            'promotions' => true,
        ];

        $shouldNotify = true;
        if (in_array($type, ['job_application', 'application_status']) && !($prefs['job_alerts'] ?? true)) {
            $shouldNotify = false;
        } elseif (in_array($type, ['chat_message', 'chat_closed']) && !($prefs['chat_messages'] ?? true)) {
            $shouldNotify = false;
        } elseif ($type === 'system_announcement' && !($prefs['system_updates'] ?? true)) {
            $shouldNotify = false;
        } elseif ($type === 'promotion' && !($prefs['promotions'] ?? true)) {
            $shouldNotify = false;
        }

        if (!$shouldNotify) {
            return;
        }

        // 1. In-app row (always)
        $this->createInApp($recipient->id, $type, $title, $body, $data);

        // 2. Push (only if FCM token exists)
        if ($recipient->fcm_token) {
            $pushData = array_merge(['type' => $type], $data);
            // FCM data values must be strings
            $pushData = array_map('strval', $pushData);
            $this->sendToToken($recipient->fcm_token, $title, $body, $pushData);
        }
    }
}
