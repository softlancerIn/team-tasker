<?php

use Livewire\Volt\Component;
use App\Models\Task;
use App\Models\Status;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $status_id = '';
    public $priority = '';
    public $tag_id = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    public $selectedTasks = [];
    public $bulkStatus = '';
    public $bulkAssignee = '';
    public $bulkPriority = '';

    protected $queryString = ['search', 'status_id', 'priority', 'tag_id', 'sortField', 'sortDirection'];

    public function updated($property)
    {
        if (in_array($property, ['search', 'status_id', 'priority', 'tag_id'])) {
            $this->resetPage();
        }
    }

    public function bulkDelete()
    {
        Task::whereIn('id', $this->selectedTasks)->delete();
        $this->selectedTasks = [];
        $this->dispatch('notify', message: 'Tasks deleted successfully');
    }

    public function bulkChangeStatus()
    {
        if (!$this->bulkStatus) {
            return;
        }
        Task::whereIn('id', $this->selectedTasks)->update(['status_id' => $this->bulkStatus]);
        $this->selectedTasks = [];
        $this->bulkStatus = '';
        $this->dispatch('notify', message: 'Statuses updated successfully');
    }

    public function bulkAssign()
    {
        if (!$this->bulkAssignee) {
            return;
        }
        Task::whereIn('id', $this->selectedTasks)->update(['assigned_to' => $this->bulkAssignee]);
        $this->selectedTasks = [];
        $this->bulkAssignee = '';
        $this->dispatch('notify', message: 'Assignees updated successfully');
    }

    public function bulkChangePriority()
    {
        if (!$this->bulkPriority) {
            return;
        }
        Task::whereIn('id', $this->selectedTasks)->update(['priority' => $this->bulkPriority]);
        $this->selectedTasks = [];
        $this->bulkPriority = '';
        $this->dispatch('notify', message: 'Priorities updated successfully');
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function with()
    {
        $userId = Auth::id();

        $tasks = Task::with(['assignedTo', 'status', 'tags'])
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhere('assigned_to', $userId);
            })
            ->when($this->search, fn($q) => $q->where('title', 'like', '%' . $this->search . '%'))
            ->when($this->status_id, fn($q) => $q->where('status_id', $this->status_id))
            ->when($this->priority, fn($q) => $q->where('priority', $this->priority))
            ->when($this->tag_id, fn($q) => $q->whereHas('tags', fn($t) => $t->where('tags.id', $this->tag_id)))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);

        return [
            'tasks' => $tasks,
            'statuses' => Status::all(),
            'tags' => Tag::all(),
            'users' => User::all(),
            'priorities' => ['Low', 'Medium', 'High', 'Critical'],
        ];
    }
};
?>

<div>
    <div class="glass-card mb-4">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                        placeholder="Search tasks...">
                </div>
            </div>
            <div class="col-md-2">
                <select wire:model.live="status_id"
                    class="form-select bg-dark text-white border-secondary border-opacity-25">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select wire:model.live="priority"
                    class="form-select bg-dark text-white border-secondary border-opacity-25">
                    <option value="">All Priorities</option>
                    @foreach ($priorities as $p)
                        <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select wire:model.live="tag_id"
                    class="form-select bg-dark text-white border-secondary border-opacity-25">
                    <option value="">All Tags</option>
                    @foreach ($tags as $tag)
                        <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 text-end">
                <button
                    wire:click="$set('search', ''); $set('status_id', ''); $set('priority', ''); $set('tag_id', '');"
                    class="btn btn-outline-secondary w-100">
                    <i class="fas fa-redo me-1"></i> Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Floating Bulk Action Bar -->
    <div id="bulkActionBar" class="bulk-action-bar {{ count($selectedTasks) > 0 ? 'show' : '' }}">
        <div class="container-fluid d-flex align-items-center justify-content-between py-3 px-4 shadow-lg">
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-primary rounded-pill px-3 py-2"
                    id="selectedCount">{{ count($selectedTasks) }}</span>
                <span class="text-white fw-medium">Tasks Selected</span>
            </div>

            <div class="d-flex align-items-center gap-3">
                <!-- Status Update -->
                <div class="d-flex align-items-center gap-2 border-end border-white border-opacity-10 pe-3">
                    <select wire:model.live="bulkStatus" class="form-select form-select-sm bulk-select">
                        <option value="">Update Status...</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                    <button wire:click="bulkChangeStatus" class="btn btn-primary btn-sm"
                        @if (!$bulkStatus) disabled @endif>
                        Apply
                    </button>
                </div>

                <!-- Priority Update -->
                <div class="d-flex align-items-center gap-2 border-end border-white border-opacity-10 pe-3">
                    <select wire:model.live="bulkPriority" class="form-select form-select-sm bulk-select">
                        <option value="">Update Priority...</option>
                        @foreach ($priorities as $p)
                            <option value="{{ $p }}">{{ $p }}</option>
                        @endforeach
                    </select>
                    <button wire:click="bulkChangePriority" class="btn btn-primary btn-sm"
                        @if (!$bulkPriority) disabled @endif>
                        Apply
                    </button>
                </div>

                <!-- Assignee Update -->
                <div class="d-flex align-items-center gap-2 border-end border-white border-opacity-10 pe-3">
                    <select wire:model.live="bulkAssignee" class="form-select form-select-sm bulk-select">
                        <option value="">Assign to...</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <button wire:click="bulkAssign" class="btn btn-primary btn-sm"
                        @if (!$bulkAssignee) disabled @endif>
                        Apply
                    </button>
                </div>

                <div class="d-flex gap-2 ms-2">
                    <button wire:click="bulkDelete"
                        onclick="return confirm('Are you sure you want to delete these tasks?')"
                        class="btn btn-danger btn-sm px-3">
                        <i class="fas fa-trash-alt me-1"></i> Delete
                    </button>
                    <button wire:click="$set('selectedTasks', [])"
                        class="btn btn-outline-secondary btn-sm">Deselect</button>
                </div>
            </div>
        </div>
    </div>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0 bg-transparent align-middle">
                <thead>
                    <tr class="text-muted small uppercase">
                        <th class="border-0 px-4" style="width: 40px;">
                            <input type="checkbox" class="form-check-input"
                                onclick="let checked = this.checked; document.querySelectorAll('.task-checkbox').forEach(c => { c.checked = checked; c.dispatchEvent(new Event('change')); })">
                        </th>
                        <th class="border-0 cursor-pointer" wire:click="sortBy('title')">
                            Title @if ($sortField === 'title')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                            @endif
                        </th>
                        <th class="border-0">Assigned To</th>
                        <th class="border-0 cursor-pointer" wire:click="sortBy('status_id')">
                            Status @if ($sortField === 'status_id')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                            @endif
                        </th>
                        <th class="border-0">Priority</th>
                        <th class="border-0">Tags</th>
                        <th class="border-0 cursor-pointer" wire:click="sortBy('created_at')">
                            Created @if ($sortField === 'created_at')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                            @endif
                        </th>
                        <th class="border-0 text-end px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                        <tr wire:key="task-row-{{ $task->id }}"
                            class="{{ in_array($task->id, $selectedTasks) ? 'bg-primary bg-opacity-5' : '' }}">
                            <td class="px-4">
                                <input type="checkbox" wire:model.live="selectedTasks" value="{{ $task->id }}"
                                    class="form-check-input task-checkbox">
                            </td>
                            <td>
                                <a href="{{ route('details', $task->id) }}"
                                    class="text-white text-decoration-none fw-medium d-block">
                                    {{ $task->title }}
                                </a>
                                @if ($task->parent_id)
                                    <span class="extra-small text-muted"><i
                                            class="fas fa-level-up-alt fa-rotate-90 me-1"></i>Subtask of
                                        #{{ $task->parent_id }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($task->assignedTo)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar"
                                            style="width: 24px; height: 24px; font-size: 0.6rem; background: rgba(99, 102, 241, 0.1); color: var(--primary);">
                                            {{ substr($task->assignedTo->name, 0, 1) }}
                                        </div>
                                        <span class="small">{{ $task->assignedTo->name }}</span>
                                    </div>
                                @else
                                    <span class="text-muted small italic">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                <span
                                    class="badge bg-{{ $task->status->color ?? 'secondary' }} bg-opacity-10 text-{{ $task->status->color ?? 'secondary' }} border border-{{ $task->status->color ?? 'secondary' }} border-opacity-25 px-3 py-1 rounded-pill extra-small">
                                    {{ $task->status->name ?? 'Unknown' }}
                                </span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-{{ $task->priority == 'Critical' ? 'danger' : ($task->priority == 'High' ? 'warning' : 'primary') }} bg-opacity-10 text-{{ $task->priority == 'Critical' ? 'danger' : ($task->priority == 'High' ? 'warning' : 'primary') }} extra-small px-2 py-1">
                                    {{ $task->priority ?? 'Medium' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach ($task->tags->take(2) as $tag)
                                        <span class="badge bg-opacity-10 extra-small px-2 py-0"
                                            style="background-color: {{ $tag->color }}1a; color: {{ $tag->color }}; border: 1px solid {{ $tag->color }}40;">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                    @if ($task->tags->count() > 2)
                                        <span class="extra-small text-muted">+{{ $task->tags->count() - 2 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-muted small">
                                {{ $task->created_at->format('M d, Y') }}
                            </td>
                            <td class="text-end px-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('details', $task->id) }}"
                                        class="btn btn-outline-info btn-sm border-0" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('edit', $task->id) }}"
                                        class="btn btn-outline-primary btn-sm border-0" title="Edit Task">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                                No tasks found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tasks->hasPages())
            <div class="p-4 border-top border-secondary border-opacity-10">
                {{ $tasks->links() }}
            </div>
        @endif
    </div>

    <style>
        .bulk-action-bar {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(150%);
            width: 90%;
            max-width: 1100px;
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            z-index: 1000;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        }

        .bulk-action-bar.show {
            transform: translateX(-50%) translateY(0);
        }

        .bulk-select {
            width: 150px;
            background: rgba(255, 255, 255, 0.05) !important;
            color: white !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        .bulk-select option {
            background: #1e293b;
            color: white;
        }

        .extra-small {
            font-size: 0.65rem;
        }

        .cursor-pointer {
            cursor: pointer;
        }
    </style>
</div>
