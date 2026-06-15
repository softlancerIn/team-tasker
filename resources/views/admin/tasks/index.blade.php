<x-admin title="Task Repository">
    <div class="top-bar-premium">
        <div>
            <h1 class="h3 fw-semibold mb-1 text-high">Administrative Tasks</h1>
            <p class="text-low mb-0" style="font-size: 0.9rem;">Global task repository and operational management.</p>
        </div>
        <div class="d-flex gap-3">
            <div class="d-flex bg-glass border border-main p-1" style="border-radius: var(--radius-md);">
                <a href="{{ route('index') }}"
                    class="btn-premium btn-premium-secondary btn-sm px-3 py-1 active"
                    style="font-size: 0.75rem; border: none; border-radius: var(--radius-sm);">
                    <i class="fas fa-list me-1"></i> List View
                </a>
                <a href="{{ route('tasks.board') }}"
                    class="btn-premium btn-premium-secondary btn-sm px-3 py-1"
                    style="font-size: 0.75rem; border: none; border-radius: var(--radius-sm);">
                    <i class="fas fa-columns me-1"></i> Kanban
                </a>
            </div>
            <a href="{{ route('create') }}" class="btn-premium btn-premium-primary px-4 py-2 shadow-sm">
                <i class="fas fa-plus-circle me-1"></i> New Task
            </a>
        </div>
    </div>

    @livewire('task-list')
</x-admin>


