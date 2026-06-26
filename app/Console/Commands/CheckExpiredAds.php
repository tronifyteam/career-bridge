<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Advertisement;
use App\Models\Job;
use Illuminate\Support\Facades\Log;

class CheckExpiredAds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ads:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and expire advertisements that have passed their expiration date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired advertisements...');

        $expiredAds = Advertisement::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        if ($expiredAds->isEmpty()) {
            $this->info('No expired advertisements found.');
            return;
        }

        foreach ($expiredAds as $ad) {
            $ad->update(['status' => 'expired']);

            // If it's a sponsored job, revoke the sponsorship status on the job
            if ($ad->type === 'sponsored_job' && $ad->job_id) {
                $job = Job::find($ad->job_id);
                if ($job) {
                    $job->update([
                        'is_sponsored' => false
                    ]);
                }
            }

            Log::info("Advertisement {$ad->id} marked as expired.");
            $this->info("Advertisement {$ad->id} marked as expired.");
        }

        $this->info('Expired advertisements check completed.');
    }
}
