<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\TimeLog;
use Illuminate\Console\Command;

class AutoStopTimers extends Command
{
    protected $signature = 'app:auto-stop-timers';

    protected $description = 'Automatically stop active task timers after office close time';

    public function handle()
    {
        $autoStopEnabled = Setting::where('key', 'auto_stop_timers')->value('value');

        // We will execute this at 8 PM, but record the time as 6:30 PM (18:30:00)
        $autoTime = \Carbon\Carbon::today()->setTime(18, 30, 0);
        $todayStr = \Carbon\Carbon::today()->format('Y-m-d');

        // 1. Auto Punch-Out for Attendances
        $attendances = \App\Models\Attendance::where(function ($q) use ($todayStr) {
                $q->where('attendance_date', $todayStr)
                  ->orWhere('date', $todayStr);
            })
            ->where(function ($q) {
                $q->whereNotNull('check_in')->orWhereNotNull('clock_in');
            })
            ->where(function ($q) {
                $q->whereNull('check_out')->whereNull('clock_out');
            })
            ->get();

        foreach ($attendances as $attendance) {
            $datePart = $attendance->attendance_date?->format('Y-m-d') 
                ?? $attendance->date?->format('Y-m-d') 
                ?? $todayStr;

            $timePart = $attendance->check_in ?? $attendance->clock_in;

            // Handle potential full datetime or time string
            if (strlen($timePart) > 8) {
                $clockIn = \Carbon\Carbon::parse($timePart);
            } else {
                $clockIn = \Carbon\Carbon::parse($datePart.' '.$timePart);
            }

            // If they clocked in after 6:30 PM, clock them out at their clock_in time (0 hours)
            $clockOut = $clockIn->greaterThan($autoTime) ? $clockIn : $autoTime;
            $workingMinutes = abs($clockIn->diffInMinutes($clockOut, false));
            $workHours = $workingMinutes / 60;

            $attendance->update([
                'check_out' => $clockOut->format('H:i:s'),
                'clock_out' => $clockOut->format('H:i:s'),
                'working_minutes' => $workingMinutes,
                'work_hours' => round($workHours, 2),
                'notes' => ltrim(($attendance->notes ?? $attendance->remarks ?? '')."\n[System: Auto clocked out at 6:30 PM]"),
                'remarks' => ltrim(($attendance->remarks ?? $attendance->notes ?? '')."\n[System: Auto clocked out at 6:30 PM]"),
            ]);
        }
        $this->info('Auto clocked out '.$attendances->count().' users.');

        // 2. Auto Stop Timers
        if ($autoStopEnabled === 'yes' || empty($autoStopEnabled)) {
            $activeTimers = TimeLog::whereNull('end_time')->get();
            foreach ($activeTimers as $timer) {
                $startTime = \Carbon\Carbon::parse($timer->start_time);

                // Set the auto end time to 6:30 PM on the EXACT day the timer started
                $timerAutoTime = $startTime->copy()->setTime(18, 30, 0);

                // End time is 6:30 PM of that day, unless started after 6:30 PM
                $endTime = $startTime->greaterThan($timerAutoTime) ? $startTime : $timerAutoTime;
                $duration = abs($endTime->diffInSeconds($startTime));

                $timer->update([
                    'end_time' => $endTime,
                    'duration' => $duration,
                ]);
            }
            $this->info('Stopped '.$activeTimers->count().' active timers at 6:30 PM.');
        }
    }
}
