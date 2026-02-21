<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\Task;
use App\Models\TimeLog;
use App\Models\User;
use App\Models\Tag;
use App\Models\TaskTemplate;
use App\Models\TaskDependency;
use App\Models\TaskAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.tasks.index');
    }

    /**
     * Display the Kanban Board.
     */
    public function board()
    {
        return view('admin.tasks.board');
    }

    /**
     * Display the Calendar View.
     */
    public function calendar()
    {
        return view('admin.tasks.calendar');
    }

    /**
     * Fetch events for FullCalendar.
     */
    public function calendarEvents(Request $request)
    {
        $userId = Auth::id();
        $tasks = Task::where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('assigned_to', $userId);
            })
            ->whereNotNull('deadline')
            ->get();

        $events = $tasks->map(function($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'start' => $task->deadline->toIso8601String(),
                'allDay' => true,
                'extendedProps' => [
                    'priority' => $task->priority,
                    'status' => $task->status->name ?? 'Unknown'
                ]
            ];
        });

        return response()->json($events);
    }

    /**
     * Display the Gantt Chart View.
     */
    public function gantt()
    {
        return view('admin.tasks.gantt');
    }

    /**
     * Fetch data for Frappe Gantt.
     */
    public function ganttData()
    {
        $userId = Auth::id();
        $tasks = Task::with('dependencies')
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('assigned_to', $userId);
            })
            ->whereNotNull('deadline')
            ->get();

        $data = $tasks->map(function($task) {
            $start = $task->created_at->format('Y-m-d');
            $end = $task->deadline->format('Y-m-d');
            
            if ($start == $end) {
                $end = $task->deadline->addDay()->format('Y-m-d');
            }

            return [
                'id' => (string)$task->id,
                'name' => $task->title,
                'start' => $start,
                'end' => $end,
                'progress' => $task->progress ?? 0,
                'dependencies' => $task->dependencies->pluck('depends_on_id')
                    ->map(fn($id) => (string)$id)->implode(', ')
            ];
        });

        return response()->json($data);
    }

    /**
     * Display a listing of the resource.
     */
    public function dashboard()
    {
        if (Auth::user()->role_id == 3) { // Client Role
             return redirect()->route('client.dashboard');
        }

        $userId = Auth::user()->id;
        $isAdmin = Auth::user()->hasRole('admin') || Auth::user()->hasRole('manager');

        // Personal tasks for the list
        $personalTasks = Task::with(['user', 'assignedTo', 'status'])
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)->orWhere('assigned_to', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Project-wide Metrics
        $totalTasks = Task::count();
        $completedTasksCount = Task::whereHas('status', function($q) {
            $q->where('slug', 'completed')->orWhere('name', 'Completed');
        })->count();
        
        $pendingTasksCount = $totalTasks - $completedTasksCount;
        $totalTickets = \App\Models\Ticket::count();
        $totalUsers = \App\Models\User::count();
        $criticalTasksCount = Task::where('priority', 'Critical')->count();
        
        $projectProgress = $totalTasks > 0 ? Task::avg('progress') : 0;
        
        // Recent Activity (Project-wide)
        $recentActivities = \App\Models\TaskLog::with(['user', 'task'])
            ->latest()
            ->take(6)
            ->get();

        // Data for chart: Last 7 days (Project-wide)
        $chartData = [];
        $chartLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartLabels[] = $date->format('D');
            $chartData[] = Task::whereDate('created_at', $date->toDateString())->count();
        }

        return view('admin.dashboard', compact(
            'personalTasks', 
            'totalTasks', 
            'completedTasksCount', 
            'pendingTasksCount', 
            'totalTickets', 
            'totalUsers', 
            'criticalTasksCount', 
            'projectProgress',
            'recentActivities',
            'chartData', 
            'chartLabels'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $users = User::all();
        $statuses = Status::orderBy('order')->get();
        $tags = Tag::all();
        $parentTasks = Task::whereNull('parent_id')->get();
        $allTasks = Task::all();
        $priorities = ['Low', 'Medium', 'High', 'Critical'];
        $templates = TaskTemplate::where('is_active', true)->get();
        
        $selectedParentId = $request->get('parent_id');

        return view('admin.tasks.create', compact('users', 'statuses', 'tags', 'parentTasks', 'allTasks', 'priorities', 'selectedParentId', 'templates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'assigned_to' => 'nullable|exists:users,id',
            'status_id' => 'required|exists:statuses,id',
            'priority' => 'nullable|string',
            'parent_id' => 'nullable|exists:tasks,id',
            'estimated_hours' => 'nullable|numeric|min:0',
            'deadline' => 'nullable|date',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'dependencies' => 'nullable|array',
            'dependencies.*' => 'exists:tasks,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB limit
            'is_recurring' => 'nullable|boolean',
            'recurring_interval' => 'nullable|string|in:daily,weekly,monthly,yearly',
        ]);

        $status = Status::find($request->status_id);
        
        $task = Task::create([
            'user_id' => Auth::id(),
            'assigned_to' => $request->assigned_to,
            'title' => $request->title,
            'description' => $request->description,
            'status_id' => $request->status_id,
            'priority' => $request->priority,
            'parent_id' => $request->parent_id,
            'estimated_hours' => $request->estimated_hours,
            'deadline' => $request->deadline,
            'is_recurring' => $request->boolean('is_recurring'),
            'recurring_interval' => $request->recurring_interval,
            'completed_at' => ($status && $status->is_completed) ? now() : null,
        ]);

        if ($request->tags) {
            $task->tags()->sync($request->tags);
        }

        if ($request->dependencies) {
            foreach ($request->dependencies as $depId) {
                TaskDependency::create([
                    'task_id' => $task->id,
                    'depends_on_id' => $depId,
                    'type' => 'blocker',
                ]);
            }
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('task-attachments', 'public');
                TaskAttachment::create([
                    'task_id' => $task->id,
                    'user_id' => Auth::id(),
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        return redirect()->route('index')->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
{
    $task = Task::with([
        'user', 
        'assignedTo', 
        'logs.user', 
        'timeLogs.user', 
        'subtasks.status', 
        'subtasks.assignedTo',
        'dependencies.blocker.status', 
        'attachments.user', 
        'tags'
    ])->findOrFail($id);

    $activeTimer = TimeLog::where('task_id', $task->id)
        ->where('user_id', Auth::id())
        ->whereNull('end_time')
        ->first();
    $statuses = Status::orderBy('order')->get();

    return view('admin.tasks.details', compact('task', 'activeTimer', 'statuses'));
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
{
    $task = Task::with(['tags', 'dependencies', 'attachments'])->findOrFail($id);

    $users = User::all();
    $statuses = Status::orderBy('order')->get();
    $tags = Tag::all();
    $parentTasks = Task::whereNull('parent_id')->where('id', '!=', $id)->get();
    $allTasks = Task::where('id', '!=', $id)->get();
    $priorities = ['Low', 'Medium', 'High', 'Critical'];

    return view('admin.tasks.edit', compact('task', 'users', 'statuses', 'tags', 'parentTasks', 'allTasks', 'priorities'));
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
{
    $task = Task::findOrFail($id);

    $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'assigned_to' => 'nullable|exists:users,id',
        'status_id' => 'required|exists:statuses,id',
        'priority' => 'nullable|string',
        'parent_id' => 'nullable|exists:tasks,id',
        'estimated_hours' => 'nullable|numeric|min:0',
        'deadline' => 'nullable|date',
        'tags' => 'nullable|array',
        'tags.*' => 'exists:tags,id',
        'dependencies' => 'nullable|array',
        'dependencies.*' => 'exists:tasks,id',
        'attachments' => 'nullable|array',
        'attachments.*' => 'file|max:10240',
        'is_recurring' => 'nullable|boolean',
        'recurring_interval' => 'nullable|string|in:daily,weekly,monthly,yearly',
    ]);

        $status = Status::find($request->status_id);
        $completed_at = $task->completed_at;
        
        if ($status && $status->is_completed && !$completed_at) {
            $completed_at = now();
        } elseif ($status && !$status->is_completed) {
            $completed_at = null;
        }

        $task->update([
            'assigned_to' => $request->assigned_to,
            'title' => $request->title,
            'description' => $request->description,
            'status_id' => $request->status_id,
            'priority' => $request->priority,
            'parent_id' => $request->parent_id,
            'estimated_hours' => $request->estimated_hours,
            'deadline' => $request->deadline,
            'is_recurring' => $request->boolean('is_recurring'),
            'recurring_interval' => $request->recurring_interval,
            'completed_at' => $completed_at,
        ]);

    if ($request->has('tags')) {
        $task->tags()->sync($request->tags);
    }

    if ($request->has('dependencies')) {
        // Simple sync for dependencies
        TaskDependency::where('task_id', $task->id)->delete();
        foreach ($request->dependencies as $depId) {
            TaskDependency::create([
                'task_id' => $task->id,
                'depends_on_id' => $depId,
                'type' => 'blocker',
            ]);
        }
    }

    if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file) {
            $path = $file->store('task-attachments', 'public');
            TaskAttachment::create([
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }
    }

    return redirect()->route('index')->with('success', 'Task updated successfully.');
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $task = Task::where('id', $request->id)->first()->delete();

        return to_route('index')->with('success', 'Data deleted successfully');
    }
}
