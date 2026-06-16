<x-admin title="Task Status Settings">
    <div class="top-bar-premium mb-4">
        <div>
            <h1 class="h3 fw-semibold mb-1 text-high">Task Status Settings</h1>
            <p class="text-low mb-0" style="font-size: 0.9rem;">Manage task statuses and workflow.</p>
        </div>
    </div>

    <div class="data-grid-wrapper p-4 mb-4" style="background: var(--bg-surface); border: 1px solid var(--border-main); border-radius: var(--radius-lg);">
        <h5 class="fw-bold mb-3" style="color: var(--text-high);">Create New Status</h5>
        <form action="{{ route('admin.settings.status.store') }}" method="POST" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-5">
                <label class="heading-label mb-2">Status Name</label>
                <input type="text" name="name" class="form-premium-control" placeholder="e.g. In Review" required>
            </div>
            <div class="col-md-2">
                <label class="heading-label mb-2">Color</label>
                <input type="color" name="color" class="form-premium-control form-control-color w-100"
                    value="#6366f1" style="padding: 0.45rem; height: 42px; cursor: pointer;">
            </div>
            <div class="col-md-5">
                <button type="submit" class="btn-premium btn-premium-primary w-100">
                    <i class="fas fa-plus me-2"></i>Add Status
                </button>
            </div>
        </form>
    </div>

    <div class="data-grid-wrapper" style="background: var(--bg-surface); border: 1px solid var(--border-main); border-radius: var(--radius-lg);">
        <h5 class="fw-bold mb-4" style="color: var(--text-high);">Manage Statuses</h5>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr style="background: var(--bg-input);">
                        <th class="py-3 heading-label ps-3">Order</th>
                        <th class="py-3 heading-label">Name</th>
                        <th class="py-3 heading-label">Color</th>
                        <th class="py-3 heading-label pe-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody style="border-top: none;">
                    @foreach ($statuses as $status)
                        <tr class="align-middle" style="border-bottom: 1px solid var(--border-subtle);">
                            <form action="{{ route('admin.settings.status.update', $status->id) }}" method="POST">
                                @csrf
                                <td class="ps-3">
                                    <input type="number" name="order" class="form-premium-control"
                                        value="{{ $status->order }}"
                                        style="width: 70px; padding: 6px 10px; font-size: 0.85rem;">
                                </td>
                                <td>
                                    <input type="text" name="name" class="form-premium-control"
                                        value="{{ $status->name }}" style="max-width: 220px;">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <input type="color" name="color" class="form-premium-control"
                                            value="{{ $status->color }}"
                                            style="width: 48px; height: 36px; padding: 3px; cursor: pointer; border-radius: var(--radius-sm);">
                                        <span class="badge-premium"
                                            style="background: {{ $status->color }}22; color: {{ $status->color }}; border: 1px solid {{ $status->color }}44; font-size: 0.7rem;">
                                            {{ $status->color }}
                                        </span>
                                    </div>
                                </td>
                                <td class="pe-3 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="submit" class="btn-premium btn-premium-secondary px-3 py-1"
                                            style="font-size: 0.8rem;" title="Save">
                                            <i class="fas fa-save me-1"></i> Save
                                        </button>
                                        @if (!$status->is_default)
                                            <button type="button" class="btn-premium px-3 py-1"
                                                style="font-size: 0.8rem; background: rgba(var(--danger-rgb), 0.1); color: var(--danger); border: 1px solid rgba(var(--danger-rgb), 0.2);"
                                                onclick="event.preventDefault(); document.getElementById('delete-status-{{ $status->id }}').submit();"
                                                title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
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
        <div class="p-3">
            {{ $statuses->links('vendor.pagination.premium') }}
        </div>
    </div>
</x-admin>
