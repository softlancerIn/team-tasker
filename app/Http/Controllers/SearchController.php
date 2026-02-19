<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');
        $tasks = collect();
        $users = collect();

        if ($query) {
            // Search Tasks
            $tasks = \App\Models\Task::where('title', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%")
                ->where(function ($q) {
                    $q->where('user_id', \Auth::id())
                        ->orWhere('assigned_to', \Auth::id());
                })
                ->with(['status', 'assignedTo'])
                ->get();

            // Search Users (if admin has permission)
            if (\Auth::user()->hasPermission('users.view')) {
                $users = \App\Models\User::where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->with('role')
                    ->get();
            }
        }

        return view('admin.search.results', compact('tasks', 'users', 'query'));
    }
}
