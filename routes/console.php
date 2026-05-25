<?php

use App\Console\Commands\ExpirePackageSubscriptions;
use App\Console\Commands\ExpirePendingBookingParticipants;
use App\Console\Commands\ExpireSaasSubscriptions;
use App\Console\Commands\NotifyExpiringSubscriptions;
use App\Console\Commands\SendBookingReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(ExpireSaasSubscriptions::class)->dailyAt('00:05')->withoutOverlapping();
Schedule::command(NotifyExpiringSubscriptions::class)->dailyAt('09:00')->withoutOverlapping();
Schedule::command(ExpirePendingBookingParticipants::class)->everyFifteenMinutes()->withoutOverlapping();
Schedule::command(ExpirePackageSubscriptions::class)->dailyAt('00:20')->withoutOverlapping();
Schedule::command(SendBookingReminders::class)->hourly()->withoutOverlapping();
