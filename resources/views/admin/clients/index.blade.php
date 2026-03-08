<x-admin title="Client Management">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="h3 fw-bold mb-0 text-high">Client Management</h2>
        @if (Auth::user()->hasPermission('clients.create'))
            <a href="{{ route('admin.clients.create') }}" class="btn-premium btn-premium-primary">
                <i class="fas fa-plus-circle me-1"></i> Add New Client
            </a>
        @endif
    </div>

    {{-- ── Filter Bar ── --}}
    <form action="{{ route('admin.clients.index') }}" method="GET"
        class="glass-card mb-4 d-flex flex-wrap align-items-center gap-3" style="border: 1px solid var(--border-main);">

        <span class="heading-label mb-0 me-1" style="font-size: 0.7rem; white-space: nowrap;">
            <i class="fas fa-filter me-1"></i> Filters:
        </span>

        <div class="position-relative flex-grow-1" style="max-width: 400px; min-width: 250px;">
            <i class="fas fa-search position-absolute text-low"
                style="left: 1rem; top: 50%; transform: translateY(-50%); font-size: 0.8rem;"></i>
            <input type="text" name="search" value="{{ request('search') }}" class="form-premium-control ps-5"
                placeholder="Search by name, email, or company..." style="font-size: 0.85rem;">
        </div>

        <div class="d-flex align-items-center gap-2 ms-auto flex-shrink-0">
            @if (request('search'))
                <a href="{{ route('admin.clients.index') }}"
                    class="text-low text-decoration-none small hover-primary transition-base"
                    style="white-space: nowrap;">
                    <i class="fas fa-times me-1"></i> Clear
                </a>
            @endif
            <button type="submit" class="btn-premium btn-premium-primary px-4"
                style="font-size: 0.85rem; white-space: nowrap;">
                <i class="fas fa-search me-1"></i> Filter Results
            </button>
        </div>
    </form>

    <div class="glass-card border-main p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr class="heading-label">
                        <th class="ps-4 py-3">Name</th>
                        <th class="py-3">Email</th>
                        <th class="py-3">Phone</th>
                        <th class="py-3">Company</th>
                        <th class="py-3">Tickets</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Created</th>
                        <th class="pe-4 py-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-premium"
                                        style="width: 32px; height: 32px; font-size: 0.8rem; background: rgba(var(--primary-rgb), 0.1); color: var(--primary);">
                                        {{ substr($client->name, 0, 1) }}
                                    </div>
                                    <span class="fw-bold text-high">{{ $client->name }}</span>
                                </div>
                            </td>
                            <td class="text-medium">{{ $client->email }}</td>
                            <td class="text-medium">{{ $client->phone ?? '-' }}</td>
                            <td class="text-medium">{{ $client->company ?? '-' }}</td>
                            <td>
                                <span class="badge-premium"
                                    style="background: var(--bg-input); color: var(--text-low);">{{ $client->tickets_count }}
                                    Tickets</span>
                            </td>
                            <td>
                                <span class="badge-premium"
                                    style="background: {{ $client->is_approved ? 'rgba(var(--accent-rgb), 0.1)' : 'rgba(var(--danger-rgb), 0.1)' }}; color: {{ $client->is_approved ? 'var(--accent)' : 'var(--danger)' }}; border: 1px solid {{ $client->is_approved ? 'rgba(var(--accent-rgb), 0.2)' : 'rgba(var(--danger-rgb), 0.2)' }};">
                                    {{ $client->is_approved ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-low">{{ $client->created_at->format('M d, Y') }}</td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    @if (Auth::user()->hasPermission('clients.edit'))
                                        <a href="{{ route('admin.clients.edit', $client->id) }}"
                                            class="btn-premium btn-premium-secondary btn-sm p-0 d-inline-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px; border-radius: 50%;">
                                            <i class="fas fa-edit" style="font-size: 0.8rem;"></i>
                                        </a>
                                    @endif
                                    @if (Auth::user()->hasPermission('clients.delete'))
                                        <button type="button"
                                            class="btn-premium btn-sm p-0 d-inline-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px; border-radius: 50%; background: rgba(var(--danger-rgb), 0.1); color: var(--danger); border: 1px solid rgba(var(--danger-rgb), 0.2);"
                                            onclick="if(confirm('Are you sure you want to delete this client?')) document.getElementById('delete-client-{{ $client->id }}').submit()">
                                            <i class="fas fa-trash" style="font-size: 0.8rem;"></i>
                                        </button>
                                    @endif
                                </div>
                                <form id="delete-client-{{ $client->id }}"
                                    action="{{ route('admin.clients.delete', $client->id) }}" method="POST"
                                    class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No clients found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($clients->hasPages())
            <div class="p-4 border-top border-main">
                {{ $clients->links() }}
            </div>
        @endif
    </div>
</x-admin>
