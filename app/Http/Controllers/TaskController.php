<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TimeLog;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = Task::with(['user', 'assignedTo'])->where('user_id', Auth::user()->id)
            ->orWhere('assigned_to', Auth::user()->id)
            ->get();

        return view('admin.tasks.index', compact('tasks'));
    }
    /**
     * Display a listing of the resource.
     */
    public function dashboard()
    {
        $userId = Auth::user()->id;
        $tasks = Task::with(['user', 'assignedTo'])
            ->where('user_id', $userId)
            ->orWhere('assigned_to', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Data for chart: Last 7 days
        $chartData = [];
        $chartLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartLabels[] = $date->format('D');
            $chartData[] = Task::whereDate('created_at', $date->toDateString())
                ->where(function($query) use ($userId) {
                    $query->where('user_id', $userId)
                          ->orWhere('assigned_to', $userId);
                })
                ->count();
        }

        return view('admin.dashboard', compact('tasks', 'chartData', 'chartLabels'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::all();
        return view('create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|max:255',
            'description' => 'required',
        ]);

        $task = Task::create([
            'user_id'     => Auth::user()->id,
            'title'       => $request->title,
            'description' => $request->description,
            'status'      => $request->status ?? 'pending',
            'assigned_to' => $request->assigned_to
        ]);

        return to_route('index')->with('success', 'Data add successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $task = Task::with(['user', 'assignedTo', 'logs.user', 'timeLogs.user'])
            ->where('id', $request->id)
            ->where(function ($query) {
                $query->where('user_id', Auth::user()->id)
                    ->orWhere('assigned_to', Auth::user()->id);
            })
            ->first();

        if (!$todo) {
            return redirect()->intended('dashboard');
        }

        $activeTimer = TimeLog::where('todo_id', $task->id)
            ->where('user_id', Auth::id())
            ->whereNull('end_time')
            ->first();

        return view('admin.tasks.details', compact('task', 'activeTimer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        $task = Task::where('id', $request->id)->where('user_id', Auth::user()->id)->first();

        if (!$todo) {
            return redirect()->intended('dashboard');
        }

        $users = User::all();
        return view('admin.tasks.edit', compact('task', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title'       => 'required|max:255',
            'description' => 'required',
        ]);

        $task = Task::where('id', $request->id)->update([
            'title'       => $request->title,
            'description' => $request->description,
            'status'      => $request->status ?? 'pending',
            'assigned_to' => $request->assigned_to
        ]);

        return to_route('index')->with('success', 'Data update successfully');
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
