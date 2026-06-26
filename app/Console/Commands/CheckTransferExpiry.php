<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DocumentType;
use App\Models\WorkerDocument;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckTransferExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-transfer-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks blue-collar worker transfer documents for impending expiry dates and sends an FCM alert if within 14, 7, or 3 days.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $transferType = DocumentType::where('slug', 'transfer_document')->first();

        if (!$transferType) {
            $this->error('Transfer Document type not found in database.');
            return;
        }

        $now = Carbon::now();
        $targetDays = [14, 7, 3];

        $this->info('Starting CheckTransferExpiry command...');

        // Fetch documents that have an expiry_date set and are currently approved
        $documents = WorkerDocument::with('user')
            ->where('document_type_id', $transferType->id)
            ->where('review_status', 'approved')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', $now->toDateString())
            ->get();

        $notificationService = app(NotificationService::class);
        $notifiedCount = 0;

        foreach ($documents as $doc) {
            $user = $doc->user;
            if (!$user || !$user->fcm_token) {
                continue;
            }

            $expiryDate = Carbon::parse($doc->expiry_date);
            $daysDiff = $now->diffInDays($expiryDate, false); // false = absolute difference if future

            if (in_array((int)$daysDiff, $targetDays)) {
                $title = "Job-Seeking Period Ending Soon";
                $body = "Your transfer/job-seeking period is ending in {$daysDiff} days ({$expiryDate->format('M d, Y')}). Please ensure you secure a job soon.";
                
                $sent = $notificationService->sendToToken(
                    $user->fcm_token,
                    $title,
                    $body,
                    ['type' => 'transfer_expiry_alert']
                );

                if ($sent) {
                    $notifiedCount++;
                    Log::info("Transfer expiry alert sent to User {$user->id} (expires in {$daysDiff} days).");
                }
            }
        }

        $this->info("Command finished. Notifications sent: {$notifiedCount}");
    }
}
