<x-admin title="Task Tag Settings">
    <div class="sticky-header shadow-sm rounded-3 d-flex justify-content-between align-items-center px-4 py-3" style="position: sticky; top: 65px; z-index: 100; background: var(--bg-surface); border: 1px solid var(--border-main);">
        <h2 class="h3 fw-bold mb-0 text-high">Task Tag Settings</h2>
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

    <div class="data-grid-wrapper mb-5">
        <div class="data-grid-top">
            <div class="data-grid-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search tags...">
            </div>
            <div class="data-grid-results">{{ $tags->count() }} Results</div>
            <div class="data-grid-actions">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table data-grid-table">
                <thead>
                    <tr>
                        <th style="width: 40px;"><input type="checkbox" class="data-grid-checkbox" id="selectAll"></th>
                        <th>NAME <i class="fas fa-sort text-low ms-1" style="font-size: 10px;"></i></th>
                        <th>COLOR <i class="fas fa-sort text-low ms-1" style="font-size: 10px;"></i></th>
                        <th class="text-end pe-4">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tags as $tag)
                        <tr class="align-middle">
                            <td><input type="checkbox" name="ids[]" value="{{ $tag->id }}" class="data-grid-checkbox item-checkbox"></td>
                            <form action="{{ route('admin.settings.tag.update', $tag->id) }}" method="POST" id="update-tag-{{ $tag->id }}">
                                @csrf
                                <td>
                                    <input type="text" name="name" class="form-premium-control"
                                        value="{{ $tag->name }}" style="max-width: 250px; background: transparent; border-color: var(--border-subtle);">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <input type="color" name="color" class="form-premium-control"
                                            value="{{ $tag->color }}"
                                            style="width: 48px; height: 36px; padding: 3px; cursor: pointer; border-radius: var(--radius-sm); background: transparent; border-color: var(--border-subtle);">
                                        <span class="badge-premium"
                                            style="background: {{ $tag->color }}22; color: {{ $tag->color }}; border: 1px solid {{ $tag->color }}44; font-size: 0.7rem;">
                                            {{ $tag->color }}
                                        </span>
                                    </div>
                                </td>
                            </form>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="submit" form="update-tag-{{ $tag->id }}" class="action-link border-0 bg-transparent" title="Save">
                                        <i class="fas fa-save text-primary"></i>
                                    </button>
                                    <form id="delete-tag-{{ $tag->id }}"
                                        action="{{ route('admin.settings.tag.delete', $tag->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="action-link delete border-0 bg-transparent"
                                            onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this tag?')) document.getElementById('delete-tag-{{ $tag->id }}').submit();"
                                            title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if ($tags->isEmpty())
                        <tr>
                            <td colspan="4" class="text-center py-5 text-medium">No tags found.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</x-admin>
