<x-admin>
    <x-slot:title>
        Task Board | Team Tasker
    </x-slot:title>

    <div class="top-bar-premium">
        <div class="d-flex align-items-center gap-3">
            <h3 class="mb-0 fw-bold">Task Board</h3>
            <div class="ms-3 d-flex gap-2">
                <a href="{{ route('index') }}" class="btn-premium btn-premium-secondary py-1 px-3 active"
                    style="font-size: 0.8rem;">
                    <i class="fas fa-columns me-1"></i> Kanban
                </a>
                <a href="{{ route('index') }}" class="btn-premium btn-premium-secondary py-1 px-3"
                    style="font-size: 0.8rem;">
                    <i class="fas fa-list me-1"></i> List
                </a>
            </div>
        </div>
        <div class="d-flex gap-2">
            <div class="search-container-premium me-2" style="width: 250px;">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search tasks..." id="kanbanSearch">
            </div>
            <a href="{{ route('create') }}" class="btn-premium btn-premium-primary">
                <i class="fas fa-plus me-1"></i> Create Task
            </a>
        </div>
    </div>

    @livewire('task-board')

</x-admin>
