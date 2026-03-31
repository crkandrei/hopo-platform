<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cron necesar pe server:
// * * * * * php artisan schedule:run >> /dev/null 2>&1

// Monitoring (comentat temporar)
// Schedule::command('pulse:check')->everyMinute();
// Schedule::command('health:queue-check-heartbeat')->everyMinute();
// Schedule::command('health:check')->everyMinute();

Schedule::command('bridges:mark-offline')->everyMinute();

Schedule::command('subscriptions:notify-expiring')->daily();

Schedule::command('pre-checkin:cleanup')->daily();

Schedule::command('reports:send-daily')
    ->dailyAt('07:00')
    ->timezone('Europe/Bucharest')
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Daily reports sent successfully');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Daily reports command failed');
        \Illuminate\Support\Facades\Mail::raw(
            'Daily reports command failed',
            fn($m) => $m->to(config('mail.from.address'))->subject('[HOPO] Daily reports failed')
        );
    });
