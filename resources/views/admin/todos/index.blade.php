<x-admin title="My Personal To-Do List">
    <div class="top-bar-premium d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 fw-semibold mb-1 text-high">My Personal To-Do</h1>
            <p class="text-low mb-0" style="font-size: 0.9rem;">Manage your private action items and notes.</p>
        </div>
        <div>
            <button type="button" class="btn-premium btn-premium-primary px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#todoModal" onclick="Livewire.dispatch('open-todo-modal')">
                <i class="fas fa-plus me-1"></i> Add Task
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 mx-auto">
            @livewire('todo-list', ['height' => '600px'])
        </div>
    </div>
</x-admin>
