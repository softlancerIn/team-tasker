<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Notifications\SlaNotification;
use Carbon\Carbon;

class ProcessSlaBreaches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:process-sla';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor task deadlines and send SLA breach notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing SLA checks...');

        // 1. Check for Breaches (now > deadline, not completed, breach not sent)
        $breachedTasks = Task::whereNotNull('deadline')
            ->whereNull('completed_at')
            ->where('deadline', '<', now())
            ->where('sla_breach_sent', false)
            ->with('assignedTo')
            ->get();

        foreach ($breachedTasks as $task) {
            if ($task->assignedTo) {
                $task->assignedTo->notify(new SlaNotification($task, 'breach'));
                $task->update(['sla_breach_sent' => true]);
                $this->warn("Notification sent for breach: #{$task->id}");
            }
        }

        // 2. Check for Warnings (deadline within next 24 hours, not completed, warning not sent)
        $warningTasks = Task::whereNotNull('deadline')
            ->whereNull('completed_at')
            ->where('deadline', '>', now())
            ->where('deadline', '<=', now()->addHours(24))
            ->where('sla_warning_sent', false)
            ->with('assignedTo')
            ->get();

        foreach ($warningTasks as $task) {
            if ($task->assignedTo) {
                $task->assignedTo->notify(new SlaNotification($task, 'warning'));
                $task->update(['sla_warning_sent' => true]);
                $this->info("Notification sent for warning: #{$task->id}");
            }
        }

        $this->info('SLA checks completed.');
    }
}
