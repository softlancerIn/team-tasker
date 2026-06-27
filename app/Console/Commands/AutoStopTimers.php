<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use App\Models\TimeLog;

class AutoStopTimers extends Command
{
    protected $signature = 'app:auto-stop-timers';
    protected $description = 'Automatically stop active task timers after office close time';

    public function handle()
    {
        $autoStopEnabled = Setting::where('key', 'auto_stop_timers')->value('value');
        
        if ($autoStopEnabled !== 'yes') {
            return;
        }

        $activeTimers = TimeLog::whereNull('end_time')->get();
        foreach ($activeTimers as $timer) {
            $endTime = now();
            $duration = abs($endTime->diffInSeconds($timer->start_time));
            $timer->update([
                'end_time' => $endTime,
                'duration' => $duration
            ]);
        }
        $this->info('Stopped ' . $activeTimers->count() . ' active timers.');
    }
}
