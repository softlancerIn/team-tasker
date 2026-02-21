<x-client title="Client Dashboard">
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="glass-card h-100">
                <div class="stat-icon icon-primary mb-2">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <h3 class="h2 text-white mb-0">{{ $tickets->count() }}</h3>
                <p class="text-muted small uppercase extra-small mb-0">Total Tickets</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card h-100">
                <div class="stat-icon icon-accent mb-2">
                    <i class="fas fa-tasks"></i>
                </div>
                <h3 class="h2 text-white mb-0">{{ $tasks->count() }}</h3>
                <p class="text-muted small uppercase extra-small mb-0">Active Tasks</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card h-100">
                <div class="stat-icon icon-warning mb-2">
                    <i class="fas fa-clock"></i>
                </div>
                <h3 class="h2 text-white mb-0">{{ $tickets->where('status', 'open')->count() }}</h3>
                <p class="text-muted small uppercase extra-small mb-0">Open Tickets</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card h-100">
                <div class="stat-icon mb-2" style="background: rgba(99, 102, 241, 0.1); color: var(--primary);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="h2 text-white mb-0">{{ $tasks->where('progress', 100)->count() }}</h3>
                <p class="text-muted small uppercase extra-small mb-0">Completed Tasks</p>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 text-white">My Support Tickets</h2>
        <a href="{{ route('client.tickets.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle me-1"></i> New Ticket
        </a>
    </div>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Priority</th>
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
                            <td>{{ $ticket->created_at->format('M d, H:i') }}</td>
                            <td>
                                <a href="{{ route('client.tickets.show', $ticket->id) }}"
                                    class="btn btn-sm btn-primary">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No tickets found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($tickets instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-3">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
        <h2 class="h4 text-white">Related Tasks</h2>
    </div>

    <div class="glass-card mb-5">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Linked Ticket</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Last Update</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ Str::limit($task->title, 50) }}</div>
                                <small class="text-muted">{{ Str::limit(strip_tags($task->description), 50) }}</small>
                            </td>
                            <td>
                                @if ($task->ticket_id)
                                    <a href="{{ route('client.tickets.show', $task->ticket_id) }}"
                                        class="text-primary decoration-none">
                                        #{{ $task->ticket_id }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge" style="background-color: {{ $task->status->color ?? '#6c757d' }}">
                                    {{ $task->status->name ?? 'Pending' }}
                                </span>
                            </td>
                            <td>
                                @if ($task->assignedTo)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar icon-primary"
                                            style="width: 25px; height: 25px; border-radius: 50%; font-size: 0.7rem;">
                                            {{ substr($task->assignedTo->name, 0, 1) }}
                                        </div>
                                        <small>{{ $task->assignedTo->name }}</small>
                                    </div>
                                @else
                                    <span class="text-muted small">Not Assigned</span>
                                @endif
                            </td>
                            <td>{{ $task->updated_at->diffForHumans() }}</td>
                            <td>
                                <a href="{{ route('client.tasks.show', $task->id) }}"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No related tasks found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-client>
