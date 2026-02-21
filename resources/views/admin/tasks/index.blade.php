<x-admin>
    <x-slot:title>
        My Tasks | Team Tasker
    </x-slot:title>

    <div class="top-bar mb-4">
        <div class="d-flex align-items-center gap-3">
            <h3 class="mb-0">My Tasks</h3>
            <div class="ms-3 d-flex gap-2">
                <a href="{{ route('index') }}" class="btn btn-outline-secondary btn-sm active">
                    <i class="fas fa-list me-1"></i> List
                </a>
                <a href="{{ route('tasks.board') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-columns me-1"></i> Kanban
                </a>
            </div>
        </div>
        <a href="{{ route('create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Create Task
        </a>
    </div>

    @livewire('task-list')
</x-admin>
