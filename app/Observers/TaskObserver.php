<?php

namespace App\Observers;

use App\Models\Task;
use App\Mail\TaskAssignedMail;
use Illuminate\Support\Facades\Mail;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        if ($task->user_id && $task->user) {
            Mail::to($task->user->email)->queue(new TaskAssignedMail($task));
        }
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        if ($task->isDirty('user_id') && $task->user_id && $task->user) {
            Mail::to($task->user->email)->queue(new TaskAssignedMail($task));
        }
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "restored" event.
     */
    public function restored(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "force deleted" event.
     */
    public function forceDeleted(Task $task): void
    {
        //
    }
}
