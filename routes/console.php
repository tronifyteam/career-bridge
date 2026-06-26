<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run the expired ads check every hour
Schedule::command('ads:check-expired')->hourly();
Schedule::command('ads:activate-scheduled')->hourly();
Schedule::command('app:check-transfer-expiry')->dailyAt('09:00');
