<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminClientController extends Controller
{
    public function index(Request $request)
    {
        // Assuming role_id 3 is Client based on previous context.
        // Better to fetch role by name if possible, but ID 3 was used in ClientController.
        $clients = User::where('role_id', 3)
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->withCount('tickets') // Assuming relationship exists, or I need to add it to User model
            ->latest()
            ->paginate(10);

        return view('admin.clients.index', compact('clients'));
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => 3, // Client Role
            'phone' => $request->phone,
            'company' => $request->company,
            'status' => $request->status ?? 'active', // Assuming detail says authorized/approved?
            // Previous auth checked verified/approved. Let's check User model for 'is_approved'.
            'is_approved' => $request->status === 'active' ? true : false,
        ]);

        return redirect()->route('admin.clients.index')->with('success', 'Client created successfully.');
    }

    public function edit($id)
    {
        $client = User::findOrFail($id);
        if ($client->role_id != 3) { // Ensure it's a client
            abort(403, 'User is not a client');
        }

        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, $id)
    {
        $client = User::findOrFail($id);
        if ($client->role_id != 3) {
            abort(403, 'User is not a client');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$id,
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company' => $request->company,
            'is_approved' => $request->status === 'active' ? true : false,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $client->update($data);

        return redirect()->route('admin.clients.index')->with('success', 'Client updated successfully.');
    }

    public function destroy($id)
    {
        $client = User::findOrFail($id);
        if ($client->role_id != 3) {
            abort(403, 'User is not a client');
        }

        // Check for tickets
        if ($client->tickets()->exists()) {
            return back()->with('error', 'Cannot delete client with associated tickets.');
        }

        $client->delete();

        return redirect()->route('admin.clients.index')->with('success', 'Client deleted successfully.');
    }
}
