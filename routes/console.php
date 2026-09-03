<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Requires a system cron calling `php artisan schedule:run` every minute in prod.
Schedule::command('echeances:generate-due')->dailyAt('06:00')->withoutOverlapping();
