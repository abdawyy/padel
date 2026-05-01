<?php

use App\Console\Commands\ExpireSaasSubscriptions;
use App\Console\Commands\NotifyExpiringSubscriptions;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(ExpireSaasSubscriptions::class)->dailyAt('00:05');
Schedule::command(NotifyExpiringSubscriptions::class)->dailyAt('09:00');
