<?php

use Livewire\Volt\Component;
use App\Models\Task;
use App\Models\Status;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $statuses;

    public function mount()
    {
        $this->statuses = Status::orderBy('order')->get();
    }

    public function updateTaskStatus($taskId, $newStatusId)
    {
        $task = Task::findOrFail($taskId);
        $status = Status::findOrFail($newStatusId);

        $task->status_id = $newStatusId;

        if ($status->is_completed && !$task->completed_at) {
            $task->completed_at = now();
        } elseif (!$status->is_completed) {
            $task->completed_at = null;
        }

        $task->save();

        // Optional: Log the change
        $task->logs()->create([
            'user_id' => Auth::id(),
            'note' => "Status changed to {$status->name} via Kanban Board",
            'type' => 'log',
        ]);

        $this->dispatch('status-updated', taskId: $taskId, statusId: $newStatusId);
    }

    public function with()
    {
        return [
            'tasks' => Task::with('assignedTo', 'status', 'tags')
                ->where(function ($q) {
                    $q->where('user_id', Auth::id())->orWhere('assigned_to', Auth::id());
                })
                ->get(),
        ];
    }
};
?>

<div class="kanban-wrapper overflow-auto pb-4" style="min-height: calc(100vh - 200px);">
    <div class="d-flex gap-4 p-1" style="min-width: fit-content;">
        @foreach ($statuses as $status)
            <div class="kanban-column" style="width: 320px; flex-shrink: 0;" wire:key="status-{{ $status->id }}">
                <div class="d-flex justify-content-between align-items-center mb-3 px-2">
                    <h6 class="mb-0 text-white d-flex align-items-center gap-2">
                        <span class="status-dot"
                            style="background-color: var(--bs-{{ $status->color ?? 'secondary' }});"></span>
                        {{ $status->name }}
                        <span class="badge bg-white bg-opacity-10 text-muted extra-small ms-1">
                            {{ $tasks->where('status_id', $status->id)->count() }}
                        </span>
                    </h6>
                    <button class="btn btn-sm btn-outline-secondary border-0 p-1 opacity-50">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>

                <div class="kanban-dropzone d-flex flex-column gap-3 p-2 rounded-3" data-status-id="{{ $status->id }}"
                    style="background: rgba(255,255,255,0.02); min-height: 500px; border: 1px dashed rgba(255,255,255,0.05);">

                    @foreach ($tasks->where('status_id', $status->id) as $task)
                        <div class="kanban-card glass-card p-3 shadow-sm cursor-grab active-grab"
                            data-id="{{ $task->id }}" wire:key="task-{{ $task->id }}">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span
                                    class="badge bg-{{ $task->priority == 'Critical' ? 'danger' : ($task->priority == 'High' ? 'warning' : 'primary') }} bg-opacity-10 text-{{ $task->priority == 'Critical' ? 'danger' : ($task->priority == 'High' ? 'warning' : 'primary') }} extra-small px-2 py-1">
                                    {{ $task->priority ?? 'Medium' }}
                                </span>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown">
                                        <i class="fas fa-chevron-down extra-small"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow">
                                        <li><a class="dropdown-item small" href="{{ route('details', $task->id) }}"><i
                                                    class="fas fa-eye me-2"></i>View</a></li>
                                        <li><a class="dropdown-item small" href="{{ route('edit', $task->id) }}"><i
                                                    class="fas fa-edit me-2"></i>Edit</a></li>
                                    </ul>
                                </div>
                            </div>

                            <a href="{{ route('details', $task->id) }}"
                                class="text-white fw-medium text-decoration-none mb-2 d-block small lh-base">
                                {{ $task->title }}
                            </a>

                            <div class="d-flex flex-wrap gap-1 mb-3">
                                @foreach ($task->tags->take(2) as $tag)
                                    <span class="badge bg-opacity-10 extra-small px-1 py-0"
                                        style="background-color: {{ $tag->color }}1a; color: {{ $tag->color }}; border: 1px solid {{ $tag->color }}40;">
                                        {{ $tag->name }}
                                    </span>
                                @endforeach
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <div class="avatar-group d-flex">
                                    @if ($task->assignedTo)
                                        <div class="avatar border-2 border-dark"
                                            style="width: 24px; height: 24px; font-size: 0.6rem;"
                                            title="{{ $task->assignedTo->name }}">
                                            {{ substr($task->assignedTo->name, 0, 1) }}
                                        </div>
                                    @else
                                        <div class="avatar border-2 border-dark bg-secondary"
                                            style="width: 24px; height: 24px; font-size: 0.6rem; opacity: 0.5;">
                                            <i class="fas fa-user extra-small"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="d-flex align-items-center gap-2 text-muted extra-small">
                                    @if ($task->timeLogs->count() > 0)
                                        <span title="Time spent"><i
                                                class="fas fa-clock me-1"></i>{{ round($task->timeLogs->sum('duration') / 3600, 1) }}h</span>
                                    @endif
                                    @if ($task->subtasks->count() > 0)
                                        <span title="Subtasks"><i
                                                class="fas fa-check-square me-1"></i>{{ $task->subtasks->where('status.is_completed', true)->count() }}/{{ $task->subtasks->count() }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        document.addEventListener('livewire:navigated', () => {
            initKanban();
        });

        // Also init on first load if using direct load
        document.addEventListener('DOMContentLoaded', () => {
            initKanban();
        });

        function initKanban() {
            const dropzones = document.querySelectorAll('.kanban-dropzone');

            dropzones.forEach(zone => {
                new Sortable(zone, {
                    group: 'kanban',
                    animation: 150,
                    ghostClass: 'kanban-ghost',
                    chosenClass: 'kanban-chosen',
                    dragClass: 'kanban-drag',
                    onEnd: function(evt) {
                        const taskId = evt.item.getAttribute('data-id');
                        const newStatusId = evt.to.getAttribute('data-status-id');

                        if (evt.from !== evt.to) {
                            @this.updateTaskStatus(taskId, newStatusId);
                        }
                    }
                });
            });
        }
    </script>
    <style>
        .kanban-ghost {
            opacity: 0.4;
            background: var(--primary) !important;
        }

        .kanban-chosen {
            transform: rotate(2deg);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .cursor-grab {
            cursor: grab;
        }

        .active-grab:active {
            cursor: grabbing;
        }
    </style>
@endpush
