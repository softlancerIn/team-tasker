<x-admin title="Task Repository">
    <div class="sticky-header shadow-sm rounded-3 mb-5 d-flex justify-content-between align-items-center px-4 py-3" style="position: sticky; top: 65px; z-index: 100; background: var(--bg-surface); border: 1px solid var(--border-main);">
        <div>
            <h2 class="h3 fw-bold mb-1" style="color: var(--text-high);">Administrative Tasks</h2>
            <p class="text-low mb-0" style="font-size: 0.85rem;">Global task repository and operational management</p>
        </div>
        <div class="d-flex gap-3">
            <div class="d-flex bg-glass border border-main rounded-pill p-1">
                <a href="{{ route('index') }}"
                    class="btn-premium btn-premium-secondary btn-sm px-3 py-1 rounded-pill active"
                    style="font-size: 0.75rem; border: none;">
                    <i class="fas fa-list me-1"></i> List View
                </a>
                <a href="{{ route('tasks.board') }}"
                    class="btn-premium btn-premium-secondary btn-sm px-3 py-1 rounded-pill"
                    style="font-size: 0.75rem; border: none;">
                    <i class="fas fa-columns me-1"></i> Kanban
                </a>
            </div>
            <a href="{{ route('create') }}" class="btn-premium btn-premium-primary px-4">
                <i class="fas fa-plus-circle me-1"></i> New Task
            </a>
        </div>
    </div>

    @livewire('task-list')
</x-admin>
