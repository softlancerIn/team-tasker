<x-admin title="Client Management">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 text-white">Client Management</h2>
        <a href="{{ route('admin.clients.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle me-1"></i> Add New Client
        </a>
    </div>

    <div class="glass-card mb-4">
        <form method="GET" action="{{ route('admin.clients.index') }}" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-white"><i
                            class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control bg-dark text-white border-secondary"
                        placeholder="Search by name or email..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
            </div>
        </form>
    </div>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Company</th>
                        <th>Tickets</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                        style="width: 32px; height: 32px; font-size: 0.8rem;">
                                        {{ substr($client->name, 0, 1) }}
                                    </div>
                                    <span class="fw-bold">{{ $client->name }}</span>
                                </div>
                            </td>
                            <td>{{ $client->email }}</td>
                            <td>{{ $client->phone ?? '-' }}</td>
                            <td>{{ $client->company ?? '-' }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $client->tickets_count }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $client->is_approved ? 'success' : 'warning' }}">
                                    {{ $client->is_approved ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $client->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('admin.clients.edit', $client->id) }}"
                                    class="btn btn-sm btn-outline-info me-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    onclick="if(confirm('Are you sure you want to delete this client?')) document.getElementById('delete-client-{{ $client->id }}').submit()">
                                    <i class="fas fa-trash"></i>
                                </button>
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
        <div class="mt-3">
            {{ $clients->links() }}
        </div>
    </div>
</x-admin>
