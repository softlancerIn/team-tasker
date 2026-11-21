<x-admin>
    <x-slot:title>
        Task Board | Team Tasker
    </x-slot:title>

    <div class="top-bar-premium mb-4">
        <div class="d-flex align-items-center gap-3">
            <h1 class="h3 fw-semibold mb-0 text-high d-flex align-items-center gap-2">
                <i class="fas fa-columns text-primary" style="font-size: 1.4rem;"></i>
                Task Board
            </h1>
            <div class="ms-2 d-none d-md-flex align-items-center">
                <div class="btn-group p-1 rounded-pill" style="background: rgba(255,255,255,0.06); border: 1px solid var(--border-main);">
                    <a href="{{ route('tasks.board') }}" class="btn btn-sm rounded-pill px-3 {{ request()->routeIs('tasks.board') ? 'btn-premium-primary text-white font-weight-bold shadow-sm' : 'text-low' }}"
                        style="font-size: 0.8rem;">
                        <i class="fas fa-columns me-1"></i> Kanban
                    </a>
                    <a href="{{ route('index') }}" class="btn btn-sm rounded-pill px-3 {{ request()->routeIs('index') ? 'btn-premium-primary text-white font-weight-bold shadow-sm' : 'text-low' }}"
                        style="font-size: 0.8rem;">
                        <i class="fas fa-list me-1"></i> List
                    </a>
                    <a href="{{ route('tasks.calendar') }}" class="btn btn-sm rounded-pill px-3 {{ request()->routeIs('tasks.calendar') ? 'btn-premium-primary text-white font-weight-bold shadow-sm' : 'text-low' }}"
                        style="font-size: 0.8rem;">
                        <i class="fas fa-calendar-alt me-1"></i> Calendar
                    </a>
                    <a href="{{ route('tasks.gantt') }}" class="btn btn-sm rounded-pill px-3 {{ request()->routeIs('tasks.gantt') ? 'btn-premium-primary text-white font-weight-bold shadow-sm' : 'text-low' }}"
                        style="font-size: 0.8rem;">
                        <i class="fas fa-project-diagram me-1"></i> Gantt
                    </a>
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('create') }}" class="btn-premium btn-premium-primary px-4 py-2 shadow-sm">
                <i class="fas fa-plus-circle me-1"></i> New Task
            </a>
        </div>
    </div>

    @livewire('task-board')

</x-admin>