<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;

// Example of inspire command (optional)
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Scheduler for offer automation
return function (Schedule $schedule) {
    $schedule->command('offer:automation')->everyMinute()->evenInMaintenanceMode();
};
