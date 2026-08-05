<?php

use App\Console\Commands\UnblockExpiredCustomers;
use App\Console\Commands\UnblockExpiredProviders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:reset-urgent-requests-counter')
    ->dailyAt('00:00');

//Admin Section:
Schedule::command(UnblockExpiredProviders::class)->hourly();
Schedule::command(UnblockExpiredCustomers::class)->hourly();
