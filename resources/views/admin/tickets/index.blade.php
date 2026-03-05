<x-admin title="Support Tickets">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="h3 fw-bold mb-1" style="color: var(--text-high);">Support Tickets</h2>
            <p class="text-low mb-0" style="font-size: 0.85rem;">Manage and respond to client support requests</p>
        </div>
        @if (Auth::user()->hasPermission('tickets.create'))
            <a href="{{ route('admin.tickets.create') }}" class="btn-premium btn-premium-primary px-4">
                <i class="fas fa-plus-circle me-1"></i> Create Ticket
            </a>
        @endif
    </div>

    <div class="glass-card mb-5" style="border: 1px solid var(--border-main);">
        <form method="GET" action="{{ route('admin.tickets.index') }}" class="row g-3 align-items-center">
            <div class="col-md-auto">
                <span class="heading-label" style="font-size: 0.7rem; color: var(--text-low);"><i
                        class="fas fa-filter me-1"></i> Filters:</span>
            </div>
            <div class="col-md-3">
                <x-select name="priority" class="form-premium-control" onchange="this.form.submit()"
                    style="font-size: 0.85rem;" placeholder="All Priorities">
                    <option value="" class="bg-dark">All Priorities</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }} class="bg-dark">Low
                        Priority</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }} class="bg-dark">
                        Medium Priority
                    </option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }} class="bg-dark">High
                        Priority</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }} class="bg-dark">
                        Urgent Priority
                    </option>
                </x-select>
            </div>
            <div class="col-md-3">
                <x-select name="status" class="form-premium-control" onchange="this.form.submit()"
                    style="font-size: 0.85rem;" placeholder="All Statuses">
                    <option value="" class="bg-dark">All Statuses</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }} class="bg-dark">Open
                        Tickets</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}
                        class="bg-dark">In Progress
                    </option>
                    <option value="waiting_for_client" {{ request('status') == 'waiting_for_client' ? 'selected' : '' }}
                        class="bg-dark">Waiting for Client</option>
                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }} class="bg-dark">
                        Resolved</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }} class="bg-dark">Closed
                    </option>
                </x-select>
            </div>
            @if (request('priority') || request('status'))
                <div class="col-md-auto">
                    <a href="{{ route('admin.tickets.index') }}"
                        class="text-low text-decoration-none small hover-primary transition-base">
                        <i class="fas fa-times me-1"></i> Clear
                    </a>
                </div>
            @endif
        </form>
    </div>

    <div class="glass-card p-0 overflow-hidden" style="border: 1px solid var(--border-main);">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr style="background: var(--bg-input);">
                        <th class="ps-4 py-3 heading-label">ID</th>
                        <th class="py-3 heading-label">Subject & Excerpt</th>
                        <th class="py-3 heading-label">Requester</th>
                        <th class="py-3 heading-label">Status</th>
                        <th class="py-3 heading-label">Priority</th>
                        <th class="py-3 heading-label">Assigned Agent</th>
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
                                    {{ Str::limit($ticket->subject, 40) }}</div>
                                <div style="color: var(--text-low); font-size: 0.75rem;">
                                    {{ Str::limit(strip_tags($ticket->body), 45) }}</div>
                            </td>
                            <td>
                                @if ($ticket->user)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-premium"
                                            style="width: 28px; height: 28px; font-size: 0.7rem;">
                                            {{ substr($ticket->user->name, 0, 1) }}
                                        </div>
                                        <span
                                            style="color: var(--text-medium); font-size: 0.85rem;">{{ $ticket->user->name }}</span>
                                    </div>
                                @else
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="stat-icon-premium m-0"
                                            style="width: 28px; height: 28px; font-size: 0.7rem; background: var(--bg-input); color: var(--text-low);">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <span class="text-low italic"
                                            style="font-size: 0.85rem;">{{ Str::limit($ticket->email_source, 15) }}</span>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge-premium"
                                    style="background: {{ $ticket->status == 'open' ? 'rgba(var(--accent-rgb), 0.1)' : 'var(--bg-input)' }}; 
                                           color: {{ $ticket->status == 'open' ? 'var(--accent)' : 'var(--text-low)' }}; white-space: nowrap;">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $tPColor = match ($ticket->priority) {
                                        'urgent' => 'var(--danger)',
                                        'high' => 'var(--accent)',
                                        default => 'var(--text-medium)',
                                    };
                                    $tPBg = match ($ticket->priority) {
                                        'urgent' => 'rgba(var(--danger-rgb), 0.1)',
                                        'high' => 'rgba(var(--accent-rgb), 0.1)',
                                        default => 'var(--bg-input)',
                                    };
                                    $tPBorder = match ($ticket->priority) {
                                        'urgent' => 'rgba(var(--danger-rgb), 0.2)',
                                        'high' => 'rgba(var(--accent-rgb), 0.2)',
                                        default => 'var(--border-subtle)',
                                    };
                                @endphp
                                <span class="badge-premium"
                                    style="background: {{ $tPBg }}; color: {{ $tPColor }}; border: 1px solid {{ $tPBorder }};">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </td>
                            <td>
                                @if ($ticket->assignedTo)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-premium"
                                            style="width: 28px; height: 28px; font-size: 0.7rem; background: var(--primary);">
                                            {{ substr($ticket->assignedTo->name, 0, 1) }}
                                        </div>
                                        <span
                                            style="color: var(--text-medium); font-size: 0.85rem;">{{ $ticket->assignedTo->name }}</span>
                                    </div>
                                @else
                                    <span class="text-low fst-italic small">Unassigned</span>
                                @endif
                            </td>
                            <td class="text-low" style="font-size: 0.85rem;">
                                {{ $ticket->created_at->format('M d, H:i') }}</td>
                            <td class="pe-4 text-end">
                                <a href="{{ route('admin.tickets.show', $ticket->id) }}"
                                    class="btn-premium btn-premium-secondary btn-sm px-3 py-1"
                                    style="font-size: 0.75rem;">
                                    Manage
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-low italic">No support tickets found
                                matching your criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($tickets->hasPages())
            <div class="p-4 border-top border-main">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</x-admin>
