<x-admin title="Task Status Settings">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 text-white">Task Status Settings</h2>
    </div>

    <div class="glass-card mb-4">
        <h5 class="text-white mb-3">Create New Status</h5>
        <form action="{{ route('admin.settings.status.store') }}" method="POST" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-5">
                <label class="form-label text-white">Status Name</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. In Review" required>
            </div>
            <div class="col-md-2">
                <label class="form-label text-white">Color</label>
                <input type="color" name="color" class="form-control form-control-color w-100" value="#6366f1">
            </div>
            <div class="col-md-5">
                <button type="submit" class="btn btn-primary w-100">Add Status</button>
            </div>
        </form>
    </div>

    <div class="glass-card">
        <h5 class="text-white mb-3">Manage Statuses</h5>
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Name</th>
                        <th>Color</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($statuses as $status)
                        <tr>
                            <form action="{{ route('admin.settings.status.update', $status->id) }}" method="POST">
                                @csrf
                                <td>
                                    <input type="number" name="order" class="form-control form-control-sm"
                                        value="{{ $status->order }}" style="width: 60px;">
                                </td>
                                <td>
                                    <input type="text" name="name" class="form-control form-control-sm"
                                        value="{{ $status->name }}">
                                </td>
                                <td>
                                    <input type="color" name="color"
                                        class="form-control form-control-color form-control-sm"
                                        value="{{ $status->color }}">
                                </td>
                                <td>
                                    <button type="submit" class="btn btn-sm btn-success me-1">
                                        <i class="fas fa-save"></i>
                                    </button>
                                    @if (!$status->is_default)
                                        <a href="{{ route('admin.settings.status.delete', $status->id) }}"
                                            class="btn btn-sm btn-danger"
                                            onclick="event.preventDefault(); document.getElementById('delete-status-{{ $status->id }}').submit();">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    @endif
                                </td>
                            </form>
                            <form id="delete-status-{{ $status->id }}"
                                action="{{ route('admin.settings.status.delete', $status->id) }}" method="POST"
                                class="d-none">
                                @csrf
                                @method('DELETE')
                            </form>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin>
