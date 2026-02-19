<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskLog;
use App\Models\TimeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskLogController extends Controller
{
    /**
     * Store a new task log (activity log).
     */
    public function storeLog(Request $request, $taskId)
    {
        $request->validate([
            'note' => 'required|string',
        ]);

        TaskLog::create([
            'task_id' => $taskId,
            'user_id' => Auth::id(),
            'note' => $request->note,
            'type' => 'log',
        ]);

        return back()->with('success', 'Log added successfully');
    }

    /**
     * Send a message to the admin.
     */
    public function sendMessage(Request $request, $taskId)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $task = Task::findOrFail($taskId);

        // Store the message as a log entry of type 'message'
        TaskLog::create([
            'task_id' => $taskId,
            'user_id' => Auth::id(),
            'note' => $request->message,
            'type' => 'message',
        ]);

        return back()->with('success', 'Message sent to admin successfully');
    }

    /**
     * Start task timer.
     */
    public function startTime($taskId)
    {
        // Check if there is already an active timer
        $activeTimer = TimeLog::where('task_id', $taskId)
            ->where('user_id', Auth::id())
            ->whereNull('end_time')
            ->first();

        if ($activeTimer) {
            return back()->with('error', 'A timer is already running for this task.');
        }

        TimeLog::create([
            'task_id' => $taskId,
            'user_id' => Auth::id(),
            'start_time' => now(),
        ]);

        // Automatically set status to in_progress if it was pending
        $task = Task::findOrFail($taskId);
        if ($task->status == 'pending') {
            $task->update(['status' => 'in_progress']);
        }

        return back()->with('success', 'Timer started successfully');
    }

    /**
     * Stop task timer.
     */
    public function stopTime($taskId)
    {
        $activeTimer = TimeLog::where('task_id', $taskId)
            ->where('user_id', Auth::id())
            ->whereNull('end_time')
            ->latest()
            ->first();

        if (! $activeTimer) {
            return back()->with('error', 'No active timer found for this task.');
        }

        $endTime = now();
        $startTime = $activeTimer->start_time;
        $duration = $endTime->diffInSeconds($startTime);

        $activeTimer->update([
            'end_time' => $endTime,
            'duration' => $duration,
        ]);

        return back()->with('success', 'Timer stopped successfully. Duration: '.gmdate('H:i:s', $duration));
    }

    /**
     * Update task progress and status.
     */
    public function updateProgress(Request $request, $taskId)
    {
        $request->validate([
            'progress' => 'required|integer|min:0|max:100',
            'status' => 'required|string|in:pending,in_progress,completed',
        ]);

        $task = Task::findOrFail($taskId);

        $oldProgress = $task->progress;
        $oldStatus = $task->status;

        $task->update([
            'progress' => $request->progress,
            'status' => $request->status,
        ]);

        // If status changed to completed, force progress to 100
        if ($request->status == 'completed' && $request->progress < 100) {
            $task->update(['progress' => 100]);
        }

        // Log the change
        TaskLog::create([
            'task_id' => $taskId,
            'user_id' => Auth::id(),
            'note' => 'Updated status to **'.ucfirst($request->status).'** and progress to **'.$task->progress.'%**.',
            'type' => 'log',
        ]);

        return back()->with('success', 'Task progress updated successfully');
    }
}
