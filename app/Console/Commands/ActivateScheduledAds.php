<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Advertisement;
use App\Models\Job;
use Illuminate\Support\Facades\Log;

class ActivateScheduledAds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ads:activate-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate scheduled sponsored jobs whose starts_at has arrived';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for scheduled sponsored jobs to activate...');

        // Find sponsored jobs that are marked active by admin, but haven't been applied to jobs table yet
        $scheduledAds = Advertisement::where('status', 'active')
            ->where('type', 'sponsored_job')
            ->whereNotNull('starts_at')
            ->where('starts_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->get();

        if ($scheduledAds->isEmpty()) {
            $this->info('No scheduled ads ready to be activated.');
            return;
        }

        foreach ($scheduledAds as $ad) {
            if ($ad->job_id) {
                $job = Job::find($ad->job_id);
                // Check if it's already sponsored to avoid redundant queries
                if ($job && !$job->is_sponsored) {
                    $job->update([
                        'is_sponsored' => true,
                        'sponsored_until' => $ad->expires_at
                    ]);
                    
                    Log::info("Scheduled Advertisement {$ad->id} has now set Job {$job->id} to sponsored.");
                    $this->info("Advertisement {$ad->id} applied to Job {$job->id}.");
                }
            }
        }

        $this->info('Scheduled advertisements check completed.');
    }
}
