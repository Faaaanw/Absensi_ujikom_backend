<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('attendance:mark-alpha-realtime')->everyFiveMinutes();
Schedule::command('attendance:autoclose')->dailyAt('23:59');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
