<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Run payment processing every day at midnight
// Queues one ProcessPayment job per eligible user
Schedule::command('payments:dispatch')
    ->dailyAt('00:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/payments-dispatch.log'));

Schedule::command('award:check-qualifications')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/award-check.log'));

// Retry any ACB upline bonuses that were missed while the queue was down
Schedule::command('acb:process')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/acb-process.log'));

// Full database backup — gzip-compressed, timestamped, auto-purges after 30 days
Schedule::command('db:backup')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/db-backup.log'));
