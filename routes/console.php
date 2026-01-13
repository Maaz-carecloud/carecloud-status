<?php

use App\Jobs\LogComponentStatusSnapshot;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule daily component status snapshot logging
Schedule::job(new LogComponentStatusSnapshot())
    ->dailyAt('00:00')
    ->name('log-component-status-snapshot')
    ->withoutOverlapping();
