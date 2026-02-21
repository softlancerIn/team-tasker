<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use Carbon\Carbon;

class ProcessRecurringTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:process-recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new tasks for recurring schedules';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing recurring tasks...');

        $recurringTasks = Task::where('is_recurring', true)
            ->where('next_occurrence_at', '<=', now())
            ->get();

        foreach ($recurringTasks as $task) {
            $this->createNextOccurrence($task);
        }

        $this->info('Recurring tasks processed successfully.');
    }

    protected function createNextOccurrence(Task $task)
    {
        // Create the new task based on the current one
        $newTask = $task->replicate();
        $newTask->status_id = 1; // Set to default status (e.g., Todo)
        $newTask->parent_id = $task->parent_id;
        $newTask->created_at = now();
        $newTask->updated_at = now();
        $newTask->next_occurrence_at = null; // Will be set for the next cycle
        $newTask->save();

        // Sync tags
        $newTask->tags()->sync($task->tags->pluck('id'));

        // Update the current task's next_occurrence_at
        $nextDate = $this->calculateNextDate($task->next_occurrence_at ?? now(), $task->recurring_interval);
        
        $task->update([
            'next_occurrence_at' => $nextDate
        ]);

        $this->info("Generated new occurrence for task: {$task->title}");
    }

    protected function calculateNextDate(Carbon $current, $interval)
    {
        return match ($interval) {
            'daily' => $current->addDay(),
            'weekly' => $current->addWeek(),
            'monthly' => $current->addMonth(),
            'yearly' => $current->addYear(),
            default => $current->addDay(),
        };
    }
}
