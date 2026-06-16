<x-client title="Client Dashboard">
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="glass-card h-100" style="border: 1px solid var(--border-main);">
                <div class="stat-icon-premium icon-primary-premium mb-3">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <h3 class="h2 fw-bold mb-1" style="color: var(--text-high);">{{ $tickets->count() }}</h3>
                <div class="heading-label" style="font-size: 0.75rem;">Total Tickets</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card h-100" style="border: 1px solid var(--border-main);">
                <div class="stat-icon-premium icon-success-premium mb-3">
                    <i class="fas fa-tasks"></i>
                </div>
                <h3 class="h2 fw-bold mb-1" style="color: var(--text-high);">{{ $tasks->count() }}</h3>
                <div class="heading-label" style="font-size: 0.75rem;">Active Tasks</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card h-100" style="border: 1px solid var(--border-main);">
                <div class="stat-icon-premium"
                    style="background: rgba(var(--danger-rgb), 0.1); color: var(--danger); margin-bottom: var(--space-3);">
                    <i class="fas fa-clock"></i>
                </div>
                <h3 class="h2 fw-bold mb-1" style="color: var(--text-high);">
                    {{ $tickets->where('status', 'open')->count() }}</h3>
                <div class="heading-label" style="font-size: 0.75rem;">Open Tickets</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card h-100" style="border: 1px solid var(--border-main);">
                <div class="stat-icon-premium icon-primary-premium mb-3">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="h2 fw-bold mb-1" style="color: var(--text-high);">
                    {{ $tasks->where('progress', 100)->count() }}</h3>
                <div class="heading-label" style="font-size: 0.75rem;">Completed Tasks</div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 fw-bold">My Support Tickets</h2>
        <a href="{{ route('client.tickets.create') }}" class="btn-premium btn-premium-primary">
            <i class="fas fa-plus-circle"></i> New Ticket
        </a>
    </div>

    <div class="glass-card p-0 overflow-hidden" style="border: 1px solid var(--border-main);">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr style="background: var(--bg-input);">
                        <th class="ps-4 py-3 heading-label">ID</th>
                        <th class="py-3 heading-label">Subject</th>
                        <th class="py-3 heading-label">Status</th>
                        <th class="py-3 heading-label">Priority</th>
                        <th class="py-3 heading-label">Created At</th>
                        <th class="pe-4 py-3 heading-label text-end">Action</th>
                    </tr>
                </thead>
                <tbody style="border-top: none;">
                    @forelse($tickets as $ticket)
                        <tr class="align-middle" style="border-bottom: 1px solid var(--border-subtle);">
                            <td class="ps-4 text-low" style="font-size: 0.85rem;">#{{ $ticket->id }}</td>
                            <td>
                                <div class="fw-bold" style="color: var(--text-high);">
                                    {{ Str::limit($ticket->subject, 50) }}</div>
                                <div style="color: var(--text-low); font-size: 0.75rem;">
                                    {{ Str::limit(strip_tags($ticket->body), 50) }}</div>
                            </td>
                            <td>
                                <span class="badge-premium"
                                    style="background: {{ $ticket->status == 'open' ? 'rgba(var(--primary-rgb), 0.1)' : 'var(--bg-input)' }}; 
                                           color: {{ $ticket->status == 'open' ? 'var(--primary)' : 'var(--text-low)' }};">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge-premium"
                                    style="background: {{ $ticket->priority == 'urgent' ? 'rgba(var(--danger-rgb), 0.1)' : 'var(--bg-input)' }};
                                           color: {{ $ticket->priority == 'urgent' ? 'var(--danger)' : 'var(--text-medium)' }};
                                           border: 1px solid {{ $ticket->priority == 'urgent' ? 'rgba(var(--danger-rgb), 0.2)' : 'var(--border-main)' }};">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </td>
                            <td class="text-low" style="font-size: 0.85rem;">
                                {{ $ticket->created_at->format('M d, H:i') }}</td>
                            <td class="pe-4 text-end">
                                <a href="{{ route('client.tickets.show', $ticket->id) }}"
                                    class="btn-premium btn-premium-secondary btn-sm px-3 py-1"
                                    style="font-size: 0.75rem;">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-low italic">No support tickets found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $tickets->appends(request()->except('tickets_page'))->links() }}
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
        <h2 class="h4 fw-bold" style="color: var(--text-high);">Related Tasks</h2>
    </div>

    <div class="glass-card p-0 overflow-hidden mb-5" style="border: 1px solid var(--border-main);">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr style="background: var(--bg-input);">
                        <th class="ps-4 py-3 heading-label">Task Details</th>
                        <th class="py-3 heading-label">Linked Context</th>
                        <th class="py-3 heading-label">Status</th>
                        <th class="py-3 heading-label">Assigned Agent</th>
                        <th class="py-3 heading-label">Last Activity</th>
                        <th class="pe-4 py-3 heading-label text-end">Action</th>
                    </tr>
                </thead>
                <tbody style="border-top: none;">
                    @forelse($tasks as $task)
                        <tr class="align-middle" style="border-bottom: 1px solid var(--border-subtle);">
                            <td class="ps-4">
                                <div class="fw-bold" style="color: var(--text-high);">
                                    {{ Str::limit($task->title, 50) }}</div>
                                <div style="color: var(--text-low); font-size: 0.75rem;">
                                    {{ Str::limit(strip_tags($task->description), 50) }}</div>
                            </td>
                            <td>
                                @if ($task->ticket_id)
                                    <a href="{{ route('client.tickets.show', $task->ticket_id) }}"
                                        class="badge-premium d-inline-flex align-items-center gap-1 text-decoration-none"
                                        style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary);">
                                        <i class="fas fa-ticket-alt" style="font-size: 0.7rem;"></i> Ticket
                                        #{{ $task->ticket_id }}
                                    </a>
                                @else
                                    <span class="text-low italic small">Direct Task</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge-premium"
                                    style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary); border: 1px solid rgba(var(--primary-rgb), 0.2);">
                                    {{ $task->status->name ?? 'Pending' }}
                                </span>
                            </td>
                            <td>
                                @if ($task->assignedTo)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-premium"
                                            style="width: 28px; height: 28px; font-size: 0.7rem;">
                                            {{ substr($task->assignedTo->name, 0, 1) }}
                                        </div>
                                        <span class="small"
                                            style="color: var(--text-medium);">{{ $task->assignedTo->name }}</span>
                                    </div>
                                @else
                                    <span class="text-low italic small">Unassigned</span>
                                @endif
                            </td>
                            <td class="text-low" style="font-size: 0.85rem;">{{ $task->updated_at->diffForHumans() }}
                            </td>
                            <td class="pe-4 text-end">
                                <a href="{{ route('client.tasks.show', $task->id) }}"
                                    class="btn-premium btn-premium-secondary btn-sm px-3 py-1"
                                    style="font-size: 0.75rem;">
                                    Track Progress
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-low italic">No active tasks tracked.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $tasks->appends(request()->except('tasks_page'))->links() }}
        </div>
    </div>
</x-client>
