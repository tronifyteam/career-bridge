<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlockedUser;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\JobApplication;
use App\Models\TranslationLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;

class ChatController extends Controller
{
    /**
     * GET /api/chats
     * Get list of chat conversations (unique users the current user has chatted with).
     */
    public function conversations(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        // Get unique conversation partners with latest message
        $conversations = ChatMessage::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($msg) use ($userId) {
                return $msg->sender_id == $userId ? $msg->receiver_id : $msg->sender_id;
            })
            ->unique()
            ->values();

        $result = [];
        foreach ($conversations as $partnerId) {
            $partner = User::find($partnerId);
            if (!$partner) continue;

            $lastMessage = ChatMessage::where(function ($q) use ($userId, $partnerId) {
                $q->where('sender_id', $userId)->where('receiver_id', $partnerId);
            })->orWhere(function ($q) use ($userId, $partnerId) {
                $q->where('sender_id', $partnerId)->where('receiver_id', $userId);
            })->orderByDesc('created_at')->first();

            $unreadCount = ChatMessage::where('sender_id', $partnerId)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->count();

            $result[] = [
                'user' => [
                    'id' => (string) $partner->id,
                    'full_name' => $partner->full_name ?? $partner->name,
                    'avatar_url' => $partner->avatar_url,
                    'role' => $partner->role,
                ],
                'last_message' => $lastMessage?->message,
                'last_message_at' => $lastMessage?->created_at->toIso8601String(),
                'unread_count' => $unreadCount,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * GET /api/chats/{userId}
     * Get messages with a specific user.
     */
    public function messages(Request $request, string $userId): JsonResponse
    {
        $currentUserId = $request->user()->id;
        $limit = min((int) $request->query('limit', 50), 100); // max 100 per page
        $beforeId = $request->query('before_id'); // cursor: load messages older than this ID

        // Get the LATEST N messages (or N messages before the cursor ID).
        // Strategy: order DESC + limit → grab newest (or older page), then re-sort ASC for display.
        $query = ChatMessage::with(['sender', 'translationLogs'])
            ->where(function ($q) use ($currentUserId, $userId) {
                $q->where('sender_id', $currentUserId)->where('receiver_id', $userId);
            })
            ->orWhere(function ($q) use ($currentUserId, $userId) {
                $q->where('sender_id', $userId)->where('receiver_id', $currentUserId);
            });

        // If a cursor is provided, only fetch messages older than that ID.
        if ($beforeId) {
            $query->where('id', '<', $beforeId);
        }

        $messages = $query
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->sortBy('created_at')
            ->values();

        // Mark as read (only messages FROM the other user TO current user)
        ChatMessage::where('sender_id', $userId)
            ->where('receiver_id', $currentUserId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'data' => $messages->map->toApiArray()->values(),
            'meta' => [
                'count' => $messages->count(),
                'has_more' => $messages->count() >= $limit,
                'oldest_id' => $messages->first()?->id,
            ],
        ]);
    }


    private function resolveUserLanguage(User $user): string
    {
        if (!empty($user->preferred_language)) {
            return $user->preferred_language;
        }

        if (!empty($user->nationality)) {
            $map = [
                'indonesia' => 'id',
                'philippines' => 'tl',
                'vietnam' => 'vi',
                'thailand' => 'th',
                'myanmar' => 'my',
                'cambodia' => 'km',
                'india' => 'hi',
                'taiwan' => 'zh-TW',
            ];
            return $map[strtolower($user->nationality)] ?? 'en';
        }

        return 'en';
    }

    /**
     * POST /api/chats/{userId}
     * Send a message to a user.
     */
    public function send(Request $request, string $userId): JsonResponse
    {
        $user = $request->user();

        $receiver = User::find($userId);
        if (!$receiver) {
            return response()->json([
                'success' => false,
                'error' => 'not_found',
                'message' => 'User not found',
            ], 404);
        }

        // PRD Rule: Chat room TIDAK terbuka otomatis. Hanya terbuka jika Employer mengklik "Open Chat" pada pelamar.
        
        // 1. If sender is a Worker, they can only reply if the Employer initiated the chat
        if ($user->isWorker()) {
            $initiated = ChatMessage::where('sender_id', $userId)
                ->where('receiver_id', $user->id)
                ->exists();
                
            if (!$initiated) {
                return response()->json([
                    'success' => false,
                    'error' => 'chat_not_opened',
                    'message' => 'Chat room belum terbuka. Pekerja hanya dapat mengirim pesan setelah Employer memulai percakapan.',
                ], 403);
            }
        }

        // Check if blocked
        $isBlocked = BlockedUser::where(function ($q) use ($user, $userId) {
            $q->where('blocker_id', $user->id)->where('blocked_id', $userId);
        })->orWhere(function ($q) use ($user, $userId) {
            $q->where('blocker_id', $userId)->where('blocked_id', $user->id);
        })->exists();

        if ($isBlocked) {
            return response()->json([
                'success' => false,
                'error' => 'user_blocked',
                'message' => 'Anda tidak dapat mengirim pesan ke pengguna ini.',
            ], 403);
        }

        // Check if chat is closed
        $conversation = ChatConversation::findPair((int) $user->id, (int) $userId);
        if ($conversation && $conversation->is_closed) {
            return response()->json([
                'success' => false,
                'error' => 'chat_closed',
                'message' => 'Percakapan ini telah ditutup.',
            ], 403);
        }

        // 2. If sender is an Employer, they can only message a worker who has applied to their job listings
        if ($user->isEmployer()) {
            $hasApplication = JobApplication::where('user_id', $userId)
                ->whereHas('job', function ($q) use ($user) {
                    $q->where('employer_id', $user->id);
                })->exists();

            if (!$hasApplication) {
                return response()->json([
                    'success' => false,
                    'error' => 'no_application',
                    'message' => 'Anda hanya dapat memulai chat dengan pekerja yang melamar ke lowongan Anda.',
                ], 403);
            }
        }

        $request->validate([
            'message'        => 'nullable|string|max:2000',
            'type'           => 'nullable|in:text,image,video,file,cv_reference',
            'attachment'     => 'nullable|file|max:20480', // Max 20MB
            'cv_data'        => 'nullable|array', // For cv_reference type
            'job_id'         => 'nullable|exists:job_listings,id',
            'application_id' => 'nullable|exists:job_applications,id',
        ]);

        if (empty($request->message) && !$request->hasFile('attachment')) {
            return response()->json([
                'success' => false,
                'error' => 'empty_message',
                'message' => 'Message or attachment is required.',
            ], 422);
        }

        $type = $request->input('type', 'text');
        $attachmentUrl = null;
        $attachmentName = null;
        $attachmentSize = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            // Store in public/chats folder
            $path = $file->store('chats', 'public');
            
            $attachmentUrl = url('/storage/' . $path);
            $attachmentName = $file->getClientOriginalName();
            $attachmentSize = $file->getSize();

            if ($type === 'text') {
                // Determine type based on mime if not explicitly provided
                $mime = $file->getMimeType();
                if (str_starts_with($mime, 'image/')) $type = 'image';
                elseif (str_starts_with($mime, 'video/')) $type = 'video';
                else $type = 'file';
            }
        }

        $translatedMessage = null;
        $translatedLanguage = null;

        $senderLang = $this->resolveUserLanguage($user);
        $receiverLang = $this->resolveUserLanguage($receiver);

        if (!empty($request->message) && $senderLang !== $receiverLang) {
            if ($this->checkAndDecrementTranslationQuota($user)) {
                $translationService = app(\App\Services\TranslationService::class);
                $translated = $translationService->translate($request->message, $receiverLang);
                if ($translated && isset($translated['text'])) {
                    $translatedMessage  = $translated['text'];
                    $translatedLanguage = $receiverLang;
                }
            }
        }

        $message = ChatMessage::create([
            'sender_id'          => $request->user()->id,
            'receiver_id'        => $userId,
            'message'            => $request->message,
            'translated_message' => $translatedMessage,
            'translated_language'=> $translatedLanguage,
            'type'               => $type,
            'attachment_url'     => $attachmentUrl,
            'attachment_name'    => $attachmentName,
            'attachment_size'    => $attachmentSize,
            'cv_data'            => $type === 'cv_reference' && $request->cv_data
                                        ? json_encode($request->cv_data)
                                        : null,
            'job_id'             => $request->input('job_id'),
            'application_id'     => $request->input('application_id'),
            'is_read'            => false,
        ]);

        // Ensure conversation record exists (for close-chat tracking)
        ChatConversation::forPair((int) $user->id, (int) $userId);

        $message->load('sender');

        // ── Log auto-translation ──────────────────────────────────────────────
        if ($translatedMessage && $request->message) {
            TranslationLog::create([
                'chat_message_id' => $message->id,
                'user_id'         => $user->id,
                'original_text'   => $request->message,
                'translated_text' => $translatedMessage,
                'source_language' => $senderLang,
                'target_language' => $translatedLanguage ?? $receiverLang,
                'trigger_type'    => 'auto',
                'created_at'      => now(),
            ]);
        }

        // Broadcast the message real-time to both sender and receiver channels.
        // Note: we do NOT use toOthers() because the mobile client does not send
        // X-Socket-ID header, which can cause toOthers() to skip broadcasting entirely.
        // Duplicate message deduplication is handled on the Flutter side.
        broadcast(new \App\Events\MessageSent($message));

        // Send push + in-app notification to receiver
        $senderName = $user->full_name ?? $user->name;
        $notifBody  = $type === 'text' ? ($request->message ?? 'Mengirimkan lampiran') : "Mengirimkan lampiran {$type}";
        app(NotificationService::class)->notify(
            $receiver,
            'chat_message',
            "Pesan Baru dari {$senderName}",
            $notifBody,
            [
                'sender_id'  => (string) $user->id,
                'message_id' => (string) $message->id,
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $message->toApiArray(),
        ], 201);
    }

    /**
     * PUT /api/chats/{messageId}/read
     * Mark a message as read.
     */
    public function markAsRead(Request $request, string $messageId): JsonResponse
    {
        $message = ChatMessage::find($messageId);

        if (!$message || $message->receiver_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'error' => 'not_found',
                'message' => 'Message not found',
            ], 404);
        }

        $message->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'data' => $message->toApiArray(),
        ]);
    }

    private function checkAndDecrementTranslationQuota(User $user): bool
    {
        $activeSub = \App\Models\Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();

        if ($activeSub) {
            if ($activeSub->chat_translation_quota > 0) {
                $activeSub->decrement('chat_translation_quota');
                return true;
            }
            return false;
        }

        // Free tier logic: 5 free translates per day
        $cacheKey = 'free_translate_' . $user->id . '_' . now()->toDateString();
        $used = \Illuminate\Support\Facades\Cache::get($cacheKey, 0);
        if ($used < 5) {
            if ($used === 0) {
                \Illuminate\Support\Facades\Cache::put($cacheKey, 1, now()->endOfDay());
            } else {
                \Illuminate\Support\Facades\Cache::increment($cacheKey);
            }
            return true;
        }

        return false;
    }

    /**
     * POST /api/chat/messages/{messageId}/translate
     * Translate a chat message content.
     */
    public function translate(Request $request, string $messageId): JsonResponse
    {
        $user = $request->user();
        $message = ChatMessage::find($messageId);

        if (!$message) {
            return response()->json([
                'success' => false,
                'error' => 'not_found',
                'message' => 'Message not found',
            ], 404);
        }

        // Verify user is either sender or receiver
        if ($message->sender_id !== $user->id && $message->receiver_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'unauthorized',
                'message' => 'You do not have permission to translate this message.',
            ], 403);
        }

        $text = $message->message;
        if (empty($text)) {
            return response()->json([
                'success' => false,
                'error' => 'empty_text',
                'message' => 'Message has no text to translate.',
            ], 400);
        }

        if (!$this->checkAndDecrementTranslationQuota($user)) {
            return response()->json([
                'success' => false,
                'error' => 'translation_quota_exceeded',
                'message' => 'You have reached your translation limit. Please purchase a pass to continue translating.',
            ], 403);
        }

        // Target language: explicit input or user language
        $targetLang = $request->input('target_lang') ?? $this->resolveUserLanguage($user);
        
        $langMap = [
            'id' => 'id',
            'en' => 'en',
            'zh' => 'zh-TW',
            'zh-tw' => 'zh-TW',
            'zh-cn' => 'zh-CN',
            'vi' => 'vi',
            'th' => 'th',
            'tl' => 'tl',
            'ja' => 'ja',
        ];
        $targetLang = $langMap[strtolower($targetLang)] ?? $targetLang;

        $translationService = app(\App\Services\TranslationService::class);

        // ── UAT #28: Skip translation if source == target language ────────────
        $sourceLangHint = $request->input('source_lang') ?? null;
        if ($sourceLangHint) {
            $normalizedSource = $langMap[strtolower($sourceLangHint)] ?? strtolower($sourceLangHint);
            $normalizedTarget = $langMap[strtolower($targetLang)] ?? strtolower($targetLang);
            if ($normalizedSource === $normalizedTarget) {
                return response()->json([
                    'success'       => true,
                    'data'          => [
                        'message_id'        => (string) $message->id,
                        'original_text'     => $text,
                        'translated_text'   => $text,
                        'detected_language' => $normalizedSource,
                        'target_language'   => $targetLang,
                        'skipped'           => true, // same language, no API call
                    ]
                ]);
            }
        }

        $translated = $translationService->translate($text, $targetLang);

        if ($translated && isset($translated['text'])) {
            $translatedText = $translated['text'];
            $detectedLang = $translated['detected_language'] ?? null;

            if ($detectedLang && empty($message->detected_language)) {
                $message->update(['detected_language' => $detectedLang]);
            }

            // ── Log manual translation ─────────────────────────────────────────
            TranslationLog::create([
                'chat_message_id' => $message->id,
                'user_id'         => $user->id,
                'original_text'   => $text,
                'translated_text' => $translatedText,
                'source_language' => $detectedLang,
                'target_language' => $targetLang,
                'trigger_type'    => 'manual',
                'created_at'      => now(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'message_id'        => (string) $message->id,
                    'original_text'     => $text,
                    'translated_text'   => $translatedText,
                    'detected_language' => $detectedLang,
                    'target_language'   => $targetLang,
                ]
            ]);
        }

        // ── UAT #29: Translation timeout / failure — return original text ──────
        // Quota was deducted; refund it since translation failed
        if ($user->translation_quota !== null) {
            $user->increment('translation_quota');
        }

        return response()->json([
            'success'       => false,
            'error'         => 'translation_failed',
            'message'       => 'Translation service is temporarily unavailable. Please retry.',
            'original_text' => $text, // allow client to show original as fallback
            'retry_allowed' => true,
        ], 503);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Block / Unblock User
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /api/users/{userId}/block
     */
    public function blockUser(Request $request, string $userId): JsonResponse
    {
        $currentUser = $request->user();

        if ((string) $currentUser->id === $userId) {
            return response()->json(['success' => false, 'message' => 'Cannot block yourself.'], 422);
        }

        if (!User::find($userId)) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        BlockedUser::firstOrCreate([
            'blocker_id' => $currentUser->id,
            'blocked_id' => $userId,
        ]);

        return response()->json(['success' => true, 'message' => 'Pengguna berhasil diblokir.']);
    }

    /**
     * DELETE /api/users/{userId}/block
     */
    public function unblockUser(Request $request, string $userId): JsonResponse
    {
        BlockedUser::where('blocker_id', $request->user()->id)
            ->where('blocked_id', $userId)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Blokir pengguna telah dicabut.']);
    }

    /**
     * GET /api/users/{userId}/block-status
     */
    public function blockStatus(Request $request, string $userId): JsonResponse
    {
        $me = $request->user()->id;
        $iBlockedThem = BlockedUser::where('blocker_id', $me)->where('blocked_id', $userId)->exists();
        $theyBlockedMe = BlockedUser::where('blocker_id', $userId)->where('blocked_id', $me)->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'i_blocked_them'  => $iBlockedThem,
                'they_blocked_me' => $theyBlockedMe,
                'is_blocked'      => $iBlockedThem || $theyBlockedMe,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Close Chat
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /api/chats/{userId}/close
     */
    public function closeChat(Request $request, string $userId): JsonResponse
    {
        $me = $request->user();
        $conversation = ChatConversation::forPair((int) $me->id, (int) $userId);

        if ($conversation->is_closed) {
            return response()->json(['success' => false, 'message' => 'Percakapan sudah ditutup.'], 422);
        }

        $conversation->update([
            'is_closed'  => true,
            'closed_by'  => $me->id,
            'closed_at'  => now(),
        ]);

        // Notify the other user that chat was closed
        $otherUser = User::find($userId);
        if ($otherUser) {
            $closerName = $me->full_name ?? $me->name;
            app(NotificationService::class)->notify(
                $otherUser,
                'chat_closed',
                'Percakapan Ditutup',
                "{$closerName} telah menutup percakapan ini.",
                ['closed_by_id' => (string) $me->id]
            );
        }

        return response()->json(['success' => true, 'message' => 'Percakapan berhasil ditutup.']);
    }

    /**
     * POST /api/chats/{userId}/reopen
     */
    public function reopenChat(Request $request, string $userId): JsonResponse
    {
        $conversation = ChatConversation::findPair((int) $request->user()->id, (int) $userId);

        if (!$conversation || !$conversation->is_closed) {
            return response()->json(['success' => false, 'message' => 'Percakapan tidak dalam keadaan tertutup.'], 422);
        }

        $conversation->update(['is_closed' => false, 'closed_by' => null, 'closed_at' => null]);

        return response()->json(['success' => true, 'message' => 'Percakapan berhasil dibuka kembali.']);
    }

    /**
     * GET /api/chats/{userId}/status
     * Returns conversation status (is_closed, is_blocked).
     */
    public function chatStatus(Request $request, string $userId): JsonResponse
    {
        $me = $request->user()->id;
        $conversation = ChatConversation::findPair((int) $me, (int) $userId);
        $isBlocked = BlockedUser::where(function ($q) use ($me, $userId) {
            $q->where('blocker_id', $me)->where('blocked_id', $userId);
        })->orWhere(function ($q) use ($me, $userId) {
            $q->where('blocker_id', $userId)->where('blocked_id', $me);
        })->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'is_closed'  => $conversation?->is_closed ?? false,
                'is_blocked' => $isBlocked,
            ],
        ]);
    }
}
