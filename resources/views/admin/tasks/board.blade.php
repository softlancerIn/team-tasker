<x-admin>
    <x-slot:title>
        Task Board | Team Tasker
    </x-slot:title>

    <div class="top-bar mb-4">
        <div class="d-flex align-items-center gap-3">
            <h3 class="mb-0">Task Board</h3>
            <div class="ms-3 d-flex gap-2">
                <a href="{{ route('index') }}" class="btn btn-outline-secondary btn-sm active">
                    <i class="fas fa-columns me-1"></i> Kanban
                </a>
                <a href="{{ route('index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-list me-1"></i> List
                </a>
            </div>
        </div>
        <div class="d-flex gap-2">
            <div class="search-wrapper me-2">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="form-control" placeholder="Search tasks..." id="kanbanSearch">
            </div>
            <a href="{{ route('create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Create Task
            </a>
        </div>
    </div>

    @livewire('task-board')

</x-admin>
