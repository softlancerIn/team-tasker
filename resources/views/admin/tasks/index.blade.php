<x-admin title="Task Repository">
    <div class="top-bar-premium">
        <div>
            <h1 class="h3 fw-semibold mb-1 text-high">Administrative Tasks</h1>
            <p class="text-low mb-0" style="font-size: 0.9rem;">Global task repository and operational management.</p>
        </div>
        <div class="d-flex gap-3 align-items-center flex-wrap">
            <div class="btn-group p-1 rounded-pill" style="background: rgba(255,255,255,0.06); border: 1px solid var(--border-main);">
                <a href="{{ route('tasks.board') }}"
                    class="btn btn-sm rounded-pill px-3 {{ request()->routeIs('tasks.board') ? 'btn-premium-primary text-white font-weight-bold shadow-sm' : 'text-low' }}"
                    style="font-size: 0.8rem;">
                    <i class="fas fa-columns me-1"></i> Kanban
                </a>
                <a href="{{ route('index') }}"
                    class="btn btn-sm rounded-pill px-3 {{ request()->routeIs('index') ? 'btn-premium-primary text-white font-weight-bold shadow-sm' : 'text-low' }}"
                    style="font-size: 0.8rem;">
                    <i class="fas fa-list me-1"></i> List
                </a>
                <a href="{{ route('tasks.calendar') }}"
                    class="btn btn-sm rounded-pill px-3 {{ request()->routeIs('tasks.calendar') ? 'btn-premium-primary text-white font-weight-bold shadow-sm' : 'text-low' }}"
                    style="font-size: 0.8rem;">
                    <i class="fas fa-calendar-alt me-1"></i> Calendar
                </a>
                <a href="{{ route('tasks.gantt') }}"
                    class="btn btn-sm rounded-pill px-3 {{ request()->routeIs('tasks.gantt') ? 'btn-premium-primary text-white font-weight-bold shadow-sm' : 'text-low' }}"
                    style="font-size: 0.8rem;">
                    <i class="fas fa-project-diagram me-1"></i> Gantt
                </a>
            </div>
            <a href="{{ route('create') }}" class="btn-premium btn-premium-primary px-4 py-2 shadow-sm">
                <i class="fas fa-plus-circle me-1"></i> New Task
            </a>
        </div>
    </div>

    @livewire('task-list')
</x-admin>


