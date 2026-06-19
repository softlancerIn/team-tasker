<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index()
    {
        return view('admin.projects.index');
    }

    public function create()
    {
        $users = User::all();
        return view('admin.projects.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date',
            'user_id' => 'nullable|exists:users,id',
        ]);

        Project::create($request->all());

        return redirect()->route('admin.projects.index')->with('success', 'Project created successfully.');
    }

    public function show($id)
    {
        $project = Project::with(['tasks.status', 'tasks.assignedTo', 'tasks.users', 'user'])->findOrFail($id);
        return view('admin.projects.show', compact('project'));
    }

    public function edit($id)
    {
        $project = Project::findOrFail($id);
        $users = User::all();
        return view('admin.projects.edit', compact('project', 'users'));
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $project->update($request->all());

        return redirect()->route('admin.projects.index')->with('success', 'Project updated successfully.');
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', 'Project deleted successfully.');
    }
}
