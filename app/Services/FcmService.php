<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;
use App\Jobs\SendFcmNotificationJob;

class FcmService
{
    /**
     * Mengirim push notification ke user spesifik melalui FCM.
     * 
     * @param User $user
     * @param string $title
     * @param string $body
     * @param array $data
     * @return bool True jika berhasil atau token tidak ada, False jika gagal.
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): bool
    {
        if (empty($user->fcm_token)) {
            Log::info("FCM: User {$user->id} does not have an FCM token. Skipping notification.");
            return false;
        }

        SendFcmNotificationJob::dispatch($user, $title, $body, $data);
        return true;
    }
}
