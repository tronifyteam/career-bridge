<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;

final class SendFcmNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        private readonly User $user,
        private readonly string $title,
        private readonly string $body,
        private readonly array $data = [],
    ) {}

    public function handle(): void
    {
        if (empty($this->user->fcm_token)) {
            Log::info("FCM: User {$this->user->id} does not have an FCM token. Skipping notification.");
            return;
        }

        $messaging = Firebase::messaging();

        $message = CloudMessage::withTarget('token', $this->user->fcm_token)
            ->withNotification(Notification::create($this->title, $this->body))
            ->withData($this->data);

        try {
            $messaging->send($message);
            Log::info("FCM: Notification sent successfully to User {$this->user->id}");
        } catch (\Throwable $e) {
            Log::error("FCM Error in Job for User {$this->user->id}: " . $e->getMessage());
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error("FCM Error for User {$this->user->id}: " . $e->getMessage());
    }
}
