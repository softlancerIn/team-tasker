<x-admin title="Tickets">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 text-white">Support Tickets</h2>
        <a href="{{ route('admin.tickets.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle me-1"></i> Create Ticket
        </a>
    </div>

    <div class="glass-card mb-4">
        <form method="GET" action="{{ route('admin.tickets.index') }}" class="row g-3">
            <div class="col-md-3">
                <select name="priority" class="form-select bg-dark text-white border-secondary"
                    onchange="this.form.submit()">
                    <option value="">All Priorities</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select bg-dark text-white border-secondary"
                    onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress
                    </option>
                    <option value="waiting_for_client"
                        {{ request('status') == 'waiting_for_client' ? 'selected' : '' }}>Waiting for Client</option>
                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
        </form>
    </div>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Subject</th>
                        <th>Requester</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Assigned To</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td>#{{ $ticket->id }}</td>
                            <td>
                                <div class="fw-bold">{{ Str::limit($ticket->subject, 50) }}</div>
                                <small class="text-muted">{{ Str::limit(strip_tags($ticket->body), 50) }}</small>
                            </td>
                            <td>
                                @if ($ticket->user)
                                    {{ $ticket->user->name }}
                                @else
                                    {{ $ticket->email_source }} <span class="badge bg-secondary">Email</span>
                                @endif
                            </td>
                            <td>
                                <span
                                    class="badge bg-{{ $ticket->status == 'open' ? 'success' : ($ticket->status == 'closed' ? 'secondary' : 'warning') }}">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-{{ $ticket->priority == 'urgent' ? 'danger' : ($ticket->priority == 'high' ? 'warning' : 'info') }}">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </td>
                            <td>
                                @if ($ticket->assignedTo)
                                    {{ $ticket->assignedTo->name }}
                                @else
                                    <span class="text-muted fst-italic">Unassigned</span>
                                @endif
                            </td>
                            <td>{{ $ticket->created_at->format('M d, H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.tickets.show', $ticket->id) }}"
                                    class="btn btn-sm btn-primary">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No tickets found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $tickets->links() }}
        </div>
    </div>
</x-admin>
