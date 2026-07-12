<?php

use Livewire\Volt\Component;
use App\Models\Todo;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $title = '';
    public $height = '250px';
    
    public $modalTitle = '';
    public $modalId = null;
    
    public $targetUserId = null;

    private function getUserId() 
    {
        return $this->targetUserId ?: Auth::id();
    }
    
    private function getUserType()
    {
        if ($this->targetUserId) {
            // Assuming impersonated users are from the main User model which typically uses 'web' 
            // or we fall back to checking if they have an admin role if needed.
            // For safety, we'll check the guard just like the original code, but 
            // since we're viewing a specific User model, 'web' is the standard user type.
            return 'web'; 
        }
        return Auth::guard('admin')->check() ? 'admin' : 'web';
    }

    public function with()
    {
        
        return [
            'todos' => Todo::where('user_id', $this->getUserId())
                        ->where('user_type', $this->getUserType())
                        ->orderBy('is_completed')
                        ->orderBy('updated_at', 'desc')
                        ->get()
        ];
    }

    public function toggleTodo($id)
    {
        $todo = Todo::where('user_id', $this->getUserId())->where('user_type', $this->getUserType())->findOrFail($id);
        $todo->update(['is_completed' => !$todo->is_completed]);
    }

    public function deleteTodo($id)
    {
        Todo::where('user_id', $this->getUserId())->where('user_type', $this->getUserType())->findOrFail($id)->delete();
    }

    #[\Livewire\Attributes\On('open-todo-modal')]
    public function openAddModal()
    {
        $this->modalId = null;
        $this->modalTitle = '';
    }

    public function openEditModal($id, $title)
    {
        $this->modalId = $id;
        $this->modalTitle = $title;
    }
    
    public function saveTodo()
    {
        $this->validate(['modalTitle' => 'required|string|max:255']);
        
        if ($this->modalId) {
            $todo = Todo::where('user_id', $this->getUserId())->where('user_type', $this->getUserType())->findOrFail($this->modalId);
            $todo->update(['title' => $this->modalTitle]);
        } else {
            Todo::create([
                'user_id' => $this->getUserId(),
                'user_type' => $this->getUserType(),
                'title' => $this->modalTitle,
                'is_completed' => false,
            ]);
        }
        
        $this->modalId = null;
        $this->modalTitle = '';
        $this->dispatch('close-todo-modal');
        $this->editingId = null;
        $this->editingTitle = '';
    }
};
?>

<div class="h-100">
    <div class="glass-card h-100" style="border: 1px solid var(--border-main); display: flex; flex-direction: column;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-0" style="color: var(--text-high);">Personal To-Do</h5>
                <p class="mb-0 text-low" style="font-size: 0.72rem; margin-top: 2px;">Your private task list</p>
            </div>
            <div class="stat-icon-premium icon-success-premium" style="width: 32px; height: 32px; font-size: 0.8rem;" role="button" data-bs-toggle="modal" data-bs-target="#todoModal" wire:click="openAddModal" title="Add To-Do">
                <i class="fas fa-plus"></i>
            </div>
        </div>

    <!-- Todo List -->
    <div class="todo-list overflow-auto" style="max-height: {{ $height }}; padding-right: 5px;">
        @forelse($todos as $todo)
            <div class="d-flex flex-column p-2 mb-2 rounded transition-all" 
                 style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-subtle); @if($todo->is_completed) opacity: 0.6; @endif">
                
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <div class="d-flex align-items-center gap-3 overflow-hidden flex-grow-1">
                        <!-- Checkbox -->
                        <div class="form-check m-0">
                            <input class="form-check-input cursor-pointer" type="checkbox" 
                                   wire:click="toggleTodo({{ $todo->id }})" 
                                   @if($todo->is_completed) checked @endif
                                   style="width: 1.1rem; height: 1.1rem; border-color: var(--border-main); background-color: var(--bg-input);">
                        </div>
                        
                        <!-- Title -->
                        <span class="text-truncate fw-medium @if($todo->is_completed) text-decoration-line-through text-low @else text-high @endif" 
                              style="font-size: 0.85rem; cursor: pointer;"
                              wire:click="toggleTodo({{ $todo->id }})">
                            {{ $todo->title }}
                        </span>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex align-items-center">
                        <button data-bs-toggle="modal" data-bs-target="#todoModal" wire:click="openEditModal({{ $todo->id }}, '{{ addslashes($todo->title) }}')" class="btn btn-sm text-low hover-primary p-1" title="Edit">
                            <i class="fas fa-edit" style="font-size: 0.75rem;"></i>
                        </button>
                        <button wire:click="deleteTodo({{ $todo->id }})" wire:confirm="Are you sure you want to delete this task?" class="btn btn-sm text-low hover-danger ms-1 p-1" title="Delete">
                            <i class="fas fa-trash-alt" style="font-size: 0.75rem;"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Metadata (Status & Time) -->
                <div class="d-flex justify-content-between align-items-center ms-4 ps-1">
                    <span class="badge-premium d-inline-flex align-items-center justify-content-center" 
                          style="font-size: 0.65rem; padding: 2px 6px; 
                          background: {{ $todo->is_completed ? 'rgba(var(--accent-rgb), 0.1)' : 'rgba(var(--warning-rgb), 0.1)' }}; 
                          color: {{ $todo->is_completed ? 'var(--accent)' : '#f59e0b' }};">
                        {{ $todo->is_completed ? 'Completed' : 'Pending' }}
                    </span>
                    <span class="text-low" style="font-size: 0.65rem;">
                        <i class="far fa-clock"></i> {{ $todo->updated_at->diffForHumans() }}
                    </span>
                </div>
            </div>
        @empty
            <div class="text-center py-4 text-low">
                <i class="fas fa-clipboard-check fa-2x mb-2 opacity-25"></i>
                <p class="mb-0" style="font-size: 0.8rem;">All caught up!</p>
            </div>
        @endforelse
    </div>

    <style>
        .hover-danger:hover {
            color: var(--danger) !important;
            background: rgba(var(--danger-rgb), 0.1);
        }
        .hover-primary:hover {
            color: var(--primary) !important;
            background: rgba(var(--primary-rgb), 0.1);
        }
        /* Custom scrollbar for todo list */
        .todo-list::-webkit-scrollbar {
            width: 4px;
        }
        .todo-list::-webkit-scrollbar-track {
            background: transparent;
        }
        .todo-list::-webkit-scrollbar-thumb {
            background: var(--border-main);
            border-radius: 4px;
        }
        .todo-list::-webkit-scrollbar-thumb:hover {
            background: var(--text-medium);
        }
    </style>
    </div> <!-- End glass-card -->

    <!-- Todo Modal -->
    <div wire:ignore.self class="modal fade" id="todoModal" tabindex="-1" aria-labelledby="todoModalLabel" aria-hidden="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card border-main">
                <div class="modal-header border-subtle">
                    <h5 class="modal-title fw-bold text-high" id="todoModalLabel">
                        @if($modalId) Edit Task @else Add Task @endif
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit="saveTodo">
                        <div class="mb-3">
                            <label class="form-label text-high fw-semibold">Task Description</label>
                            <input type="text" wire:model="modalTitle" class="form-premium-control w-100" placeholder="E.g., Review project documentation..." required>
                            @error('modalTitle') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn-premium btn-premium-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn-premium btn-premium-primary px-4">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Script to close modal via browser event -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('close-todo-modal', () => {
                const modalEl = document.getElementById('todoModal');
                if (modalEl) {
                    let modal = window.bootstrap.Modal.getInstance(modalEl);
                    if (!modal) {
                        modal = new window.bootstrap.Modal(modalEl);
                    }
                    modal.hide();
                    
                    // Fallback to remove stuck backdrop
                    setTimeout(() => {
                        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                        document.body.classList.remove('modal-open');
                        document.body.style.overflow = '';
                        document.body.style.paddingRight = '';
                    }, 150);
                }
            });
        });
    </script>
</div>
