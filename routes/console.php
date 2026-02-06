<?php

use App\Jobs\MachineIdleMonitoringJob;
use App\Jobs\RouteSpeedMonitoringJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule route speed monitoring job to run every 5 minutes
Schedule::job(new RouteSpeedMonitoringJob())
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer();

// Schedule machine idle monitoring job to run every 10 minutes
Schedule::job(new MachineIdleMonitoringJob())
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->onOneServer();
