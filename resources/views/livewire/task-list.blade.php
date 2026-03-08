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

    public function paginationView()
    {
        return 'vendor.pagination.premium';
    }

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
                <div class="search-container-premium">
                    <i class="fas fa-search"></i>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search tasks...">
                </div>
            </div>
            <div class="col-md-2">
                <x-select wire:model.live="status_id" name="status_id" id="status_id" placeholder="All Statuses">
                    <option value="" class="bg-dark">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->id }}" class="bg-dark">{{ $status->name }}</option>
                    @endforeach
                </x-select>
            </div>
            <div class="col-md-2">
                <x-select wire:model.live="priority" placeholder="All Priorities">
                    <option value="" class="bg-dark">All Priorities</option>
                    @foreach ($priorities as $p)
                        <option value="{{ $p }}" class="bg-dark">{{ $p }}</option>
                    @endforeach
                </x-select>
            </div>
            <div class="col-md-2">
                <x-select wire:model.live="tag_id" placeholder="All Tags">
                    <option value="" class="bg-dark">All Tags</option>
                    @foreach ($tags as $tag)
                        <option value="{{ $tag->id }}" class="bg-dark">{{ $tag->name }}</option>
                    @endforeach
                </x-select>
            </div>
            <div class="col-md-2 text-end">
                <button
                    wire:click="$set('search', ''); $set('status_id', ''); $set('priority', ''); $set('tag_id', '');"
                    class="btn-premium btn-premium-secondary w-100">
                    <i class="fas fa-redo me-1"></i> Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Floating Bulk Action Bar -->
    <div id="bulkActionBar" class="bulk-action-bar hidden shadow-premium {{ count($selectedTasks) > 0 ? 'show' : '' }}"
        style="border: 1px solid var(--border-main); background: var(--bg-surface);">
        <div class="container-fluid d-flex align-items-center justify-content-between py-3 px-4">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon-premium m-0 d-flex align-items-center justify-content-center"
                    style="width: 40px; height: 40px; background: rgba(var(--primary-rgb), 0.1); color: var(--primary);">
                    <span id="selectedCount" class="fw-bold">{{ count($selectedTasks) }}</span>
                </div>
                <span class="text-high fw-bold" style="font-size: 0.95rem;">Tasks Selected</span>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-4">
                <!-- Status Update -->
                <div class="d-flex align-items-center gap-2 border-end border-main pe-4">
                    <x-select wire:model.live="bulkStatus" placeholder="Status..."
                        style="width: 150px; font-size: 0.8rem; background: var(--bg-input);">
                        <option value="" class="bg-dark">Status...</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->id }}" class="bg-dark">{{ $status->name }}</option>
                        @endforeach
                    </x-select>
                    <button wire:click="bulkChangeStatus" class="btn-premium btn-premium-primary py-2 px-3"
                        style="font-size: 0.8rem;" @if (!$bulkStatus) disabled @endif>
                        Apply
                    </button>
                </div>

                <!-- Priority Update -->
                <div class="d-flex align-items-center gap-2 border-end border-main pe-4">
                    <x-select wire:model.live="bulkPriority" placeholder="Priority..."
                        style="width: 150px; font-size: 0.8rem; background: var(--bg-input);">
                        <option value="" class="bg-dark">Priority...</option>
                        @foreach ($priorities as $p)
                            <option value="{{ $p }}" class="bg-dark">{{ $p }}</option>
                        @endforeach
                    </x-select>
                    <button wire:click="bulkChangePriority" class="btn-premium btn-premium-primary py-2 px-3"
                        style="font-size: 0.8rem;" @if (!$bulkPriority) disabled @endif>
                        Apply
                    </button>
                </div>

                <div class="d-flex gap-2">
                    <button wire:click="bulkDelete"
                        onclick="return confirm('Are you sure you want to delete these tasks?')"
                        class="btn-premium py-2 px-4"
                        style="background: rgba(var(--danger-rgb), 0.1); color: var(--danger); border: 1px solid rgba(var(--danger-rgb), 0.2); font-size: 0.8rem;">
                        <i class="fas fa-trash-alt me-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="glass-card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr class="heading-label">
                        <th class="border-0 px-4" style="width: 40px;">
                            <input type="checkbox" class="form-check-input"
                                onclick="let checked = this.checked; document.querySelectorAll('.task-checkbox').forEach(c => { c.checked = checked; c.dispatchEvent(new Event('change')); })">
                        </th>
                        <th class="border-0 cursor-pointer" wire:click="sortBy('title')"
                            style="color: var(--text-high);">
                            Title @if ($sortField === 'title')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                            @endif
                        </th>
                        <th class="border-0" style="color: var(--text-high);">Assigned To</th>
                        <th class="border-0 cursor-pointer" wire:click="sortBy('status_id')"
                            style="color: var(--text-high);">
                            Status @if ($sortField === 'status_id')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                            @endif
                        </th>
                        <th class="border-0" style="color: var(--text-high);">Priority</th>
                        <th class="border-0" style="color: var(--text-high);">Tags</th>
                        <th class="border-0 cursor-pointer" wire:click="sortBy('created_at')"
                            style="color: var(--text-high);">
                            Created @if ($sortField === 'created_at')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                            @endif
                        </th>
                        <th class="border-0 text-end px-4" style="color: var(--text-high);">Actions</th>
                    </tr>
                </thead>
                <tbody style="border-top: none;">
                    @forelse($tasks as $task)
                        <tr wire:key="task-row-{{ $task->id }}"
                            class="{{ in_array($task->id, $selectedTasks) ? 'bg-primary-subtle' : '' }}"
                            style="border-bottom: 1px solid var(--border-subtle);">
                            <td class="px-4">
                                <input type="checkbox" wire:model.live="selectedTasks" value="{{ $task->id }}"
                                    class="form-check-input task-checkbox">
                            </td>
                            <td>
                                <a href="{{ route('details', $task->id) }}"
                                    class="text-decoration-none fw-medium d-block text-high">
                                    {{ $task->title }}
                                </a>
                                @if ($task->parent_id)
                                    <span class="extra-small text-low"><i
                                            class="fas fa-level-up-alt fa-rotate-90 me-1"></i>Subtask of
                                        #{{ $task->parent_id }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($task->assignedTo)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-premium"
                                            style="width: 24px; height: 24px; font-size: 0.6rem;">
                                            {{ substr($task->assignedTo->name, 0, 1) }}
                                        </div>
                                        <span class="small fw-medium">{{ $task->assignedTo->name }}</span>
                                    </div>
                                @else
                                    <span class="text-low small italic">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusColorMap = [
                                        'primary' => 'var(--primary)',
                                        'success' => 'var(--accent)',
                                        'danger' => 'var(--danger)',
                                        'warning' => '#f59e0b',
                                        'info' => '#0ea5e9',
                                        'secondary' => 'var(--text-medium)',
                                    ];
                                    $statusColor = $task->status->color ?? 'secondary';
                                    $themeColor = $statusColorMap[$statusColor] ?? 'var(--text-medium)';
                                @endphp
                                <span class="badge-premium"
                                    style="background: color-mix(in srgb, {{ $themeColor }} 15%, transparent); color: {{ $themeColor }}; border: 1px solid color-mix(in srgb, {{ $themeColor }} 30%, transparent);">
                                    {{ $task->status->name ?? 'Unknown' }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $pColor =
                                        $task->priority == 'Critical'
                                            ? 'var(--danger)'
                                            : ($task->priority == 'High'
                                                ? 'var(--accent)'
                                                : 'var(--primary)');
                                    $pBg =
                                        $task->priority == 'Critical'
                                            ? 'rgba(var(--danger-rgb), 0.1)'
                                            : ($task->priority == 'High'
                                                ? 'rgba(var(--accent-rgb), 0.1)'
                                                : 'rgba(var(--primary-rgb), 0.1)');
                                    $pBorder =
                                        $task->priority == 'Critical'
                                            ? 'rgba(var(--danger-rgb), 0.2)'
                                            : ($task->priority == 'High'
                                                ? 'rgba(var(--accent-rgb), 0.2)'
                                                : 'rgba(var(--primary-rgb), 0.2)');
                                @endphp
                                <span class="badge-premium"
                                    style="background: {{ $pBg }}; color: {{ $pColor }}; border: 1px solid {{ $pBorder }}; font-size: 0.7rem;">
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
                                        <span class="extra-small text-low">+{{ $task->tags->count() - 2 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-low small">
                                {{ $task->created_at->format('M d, Y') }}
                            </td>
                            <td class="text-end px-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('details', $task->id) }}"
                                        class="btn-premium btn-premium-secondary btn-sm p-0 d-inline-flex align-items-center justify-content-center"
                                        style="width: 32px; height: 32px; border-radius: 50%;" title="View Details">
                                        <i class="fas fa-eye" style="font-size: 0.8rem;"></i>
                                    </a>
                                    <a href="{{ route('edit', $task->id) }}"
                                        class="btn-premium btn-premium-secondary btn-sm p-0 d-inline-flex align-items-center justify-content-center"
                                        style="width: 32px; height: 32px; border-radius: 50%; color: var(--primary);"
                                        title="Edit Task">
                                        <i class="fas fa-edit" style="font-size: 0.8rem;"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-low">
                                <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                                No tasks found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tasks->hasPages())
            <div class="p-4" style="border-top: 1px solid var(--border-subtle);">
                {{ $tasks->links() }}
            </div>
        @endif
    </div>

    <style>
        .bulk-action-bar {
            position: fixed;
            bottom: var(--space-6);
            left: 50%;
            transform: translateX(-50%) translateY(200%);
            width: auto;
            min-width: 500px;
            z-index: 1050;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: var(--radius-xl);
            backdrop-filter: blur(15px);
        }

        .bulk-action-bar.show {
            transform: translateX(-50%) translateY(0);
        }

        .cursor-pointer {
            cursor: pointer;
        }
    </style>
</div>
