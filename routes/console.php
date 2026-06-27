<?php

use App\Models\Setting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

try {
    $closeTime = Setting::where('key', 'office_close_time')->value('value') ?: '19:00';
    Schedule::command('app:auto-stop-timers')->dailyAt($closeTime);
} catch (\Exception $e) {
    // Fallback if database is not migrated yet
    Schedule::command('app:auto-stop-timers')->dailyAt('19:00');
}
