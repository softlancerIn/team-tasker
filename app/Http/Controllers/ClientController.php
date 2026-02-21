<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    public function dashboard()
    {
        $tickets = Ticket::where('user_id', Auth::id())->latest()->get();
        
        $ticketIds = $tickets->pluck('id');
        $tasks = \App\Models\Task::whereIn('ticket_id', $ticketIds)
            ->with(['status', 'assignedTo'])
            ->latest()
            ->get();

        return view('client.dashboard', compact('tickets', 'tasks'));
    }

    public function create()
    {
        return view('client.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        Ticket::create([
            'user_id' => Auth::id(),
            'subject' => $request->subject,
            'body' => $request->body,
            'status' => 'open',
            'priority' => $request->priority,
        ]);

        return redirect()->route('client.dashboard')->with('success', 'Ticket submitted successfully.');
    }

    public function show($id)
    {
        $ticket = Ticket::with(['replies.user'])->where('user_id', Auth::id())->findOrFail($id);
        return view('client.tickets.show', compact('ticket'));
    }

    public function showTask($id)
    {
        $task = \App\Models\Task::with(['status', 'assignedTo', 'attachments', 'logs' => function($q) {
            $q->where('type', 'message')->with('user')->latest();
        }])->whereHas('ticket', function($q) {
            $q->where('user_id', Auth::id());
        })->findOrFail($id);

        return view('client.tasks.show', compact('task'));
    }

    public function replyTask(Request $request, $id)
    {
        $request->validate([
            'note' => 'required|string',
        ]);

        $task = \App\Models\Task::whereHas('ticket', function($q) {
            $q->where('user_id', Auth::id());
        })->findOrFail($id);

        \App\Models\TaskLog::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'note' => $request->note,
            'type' => 'message', // Client messages are always public
        ]);

        return back()->with('success', 'Reply sent successfully');
    }

    public function reply(Request $request, $id)
    {
        $request->validate(['body' => 'required|string']);

        $ticket = Ticket::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $ticket->replies()->create([
            'user_id' => Auth::id(),
            'body' => $request->body,
            'type' => 'client_reply',
        ]);

        // Optional: Update ticket status to 'open' or 'in_progress' if it was closed?
        if ($ticket->status == 'resolved' || $ticket->status == 'closed') {
            $ticket->update(['status' => 'open']);
        }

        return back()->with('success', 'Reply sent successfully.');
    }

    // Login page for client if distinct from general login, 
    // but plan uses standard auth.
    // We might want a specific layout for client dashboard though.
}
