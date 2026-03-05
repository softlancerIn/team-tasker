<x-admin title="Task Tag Settings">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 fw-bold mb-0" style="color: var(--text-high);">Task Tag Settings</h2>
    </div>

    <div class="glass-card mb-4" style="border: 1px solid var(--border-main);">
        <h5 class="fw-bold mb-3" style="color: var(--text-high);">Create New Tag</h5>
        <form action="{{ route('admin.settings.tag.store') }}" method="POST" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-5">
                <label class="heading-label mb-2">Tag Name</label>
                <input type="text" name="name" class="form-premium-control" placeholder="e.g. Critical" required>
            </div>
            <div class="col-md-2">
                <label class="heading-label mb-2">Color</label>
                <input type="color" name="color" class="form-premium-control form-control-color w-100"
                    value="#6366f1" style="padding: 0.45rem; height: 42px; cursor: pointer;">
            </div>
            <div class="col-md-5">
                <button type="submit" class="btn-premium btn-premium-primary w-100">
                    <i class="fas fa-plus me-2"></i>Add Tag
                </button>
            </div>
        </form>
    </div>

    <div class="glass-card" style="border: 1px solid var(--border-main);">
        <h5 class="fw-bold mb-4" style="color: var(--text-high);">Manage Tags</h5>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr style="background: var(--bg-input);">
                        <th class="py-3 heading-label ps-3">Name</th>
                        <th class="py-3 heading-label">Color</th>
                        <th class="py-3 heading-label pe-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody style="border-top: none;">
                    @foreach ($tags as $tag)
                        <tr class="align-middle" style="border-bottom: 1px solid var(--border-subtle);">
                            <form action="{{ route('admin.settings.tag.update', $tag->id) }}" method="POST">
                                @csrf
                                <td class="ps-3">
                                    <input type="text" name="name" class="form-premium-control"
                                        value="{{ $tag->name }}" style="max-width: 250px;">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <input type="color" name="color" class="form-premium-control"
                                            value="{{ $tag->color }}"
                                            style="width: 48px; height: 36px; padding: 3px; cursor: pointer; border-radius: var(--radius-sm);">
                                        <span class="badge-premium"
                                            style="background: {{ $tag->color }}22; color: {{ $tag->color }}; border: 1px solid {{ $tag->color }}44; font-size: 0.7rem;">
                                            {{ $tag->color }}
                                        </span>
                                    </div>
                                </td>
                                <td class="pe-3 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="submit" class="btn-premium btn-premium-secondary px-3 py-1"
                                            style="font-size: 0.8rem;" title="Save">
                                            <i class="fas fa-save me-1"></i> Save
                                        </button>
                                        <button type="button" class="btn-premium px-3 py-1"
                                            style="font-size: 0.8rem; background: rgba(var(--danger-rgb), 0.1); color: var(--danger); border: 1px solid rgba(var(--danger-rgb), 0.2);"
                                            onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this tag?')) document.getElementById('delete-tag-{{ $tag->id }}').submit();"
                                            title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </form>
                            <form id="delete-tag-{{ $tag->id }}"
                                action="{{ route('admin.settings.tag.delete', $tag->id) }}" method="POST"
                                class="d-none">
                                @csrf
                                @method('DELETE')
                            </form>
                        </tr>
                    @endforeach
                    @if ($tags->isEmpty())
                        <tr>
                            <td colspan="3" class="text-center py-4 text-low">No tags found.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</x-admin>
