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

        $task->logs()->create([
            'user_id' => Auth::id(),
            'note' => "Status changed to <strong>{$status->name}</strong> via Kanban Board",
            'type' => 'log',
        ]);

        $this->dispatch('status-updated', taskId: $taskId, statusId: $newStatusId, statusName: $status->name, statusColor: $status->color);
    }

    public function with()
    {
        return [
            'tasks' => Task::with('assignedTo', 'status', 'tags', 'subtasks', 'timeLogs', 'attachments')
                ->where(function ($q) {
                    $q->where('user_id', Auth::id())->orWhere('assigned_to', Auth::id());
                })
                ->get(),
        ];
    }
};
?>

{{-- Single Livewire root --}}
<div>

    {{-- ─── DRAG-AND-DROP TOAST ─── --}}
    <div id="dnd-toast" class="dnd-toast" style="display:none;">
        <i class="fas fa-check-circle me-2" style="color: var(--accent);"></i>
        <span id="dnd-toast-msg">Task moved</span>
    </div>

    <div class="kanban-wrapper overflow-auto pb-5" style="min-height: calc(100vh - 180px);">
        <div class="d-flex gap-4 p-1 pb-4" style="min-width: fit-content; align-items: flex-start;">
            @foreach ($statuses as $status)
                @php
                    $columnColor = ltrim($status->color ?? '#6366f1', '#');
                    $columnCount = $tasks->where('status_id', $status->id)->count();
                @endphp
                <div class="kanban-column" style="width: 300px; flex-shrink: 0;" wire:key="col-{{ $status->id }}">

                    {{-- Column Header --}}
                    <div class="kanban-col-header d-flex align-items-center justify-content-between mb-3 px-1">
                        <div class="d-flex align-items-center gap-2">
                            <div class="kanban-status-dot"
                                style="background: #{{ $columnColor }}; box-shadow: 0 0 6px #{{ $columnColor }}66;">
                            </div>
                            <span class="fw-bold"
                                style="font-size: 0.82rem; color: var(--text-high); letter-spacing: 0.03em; text-transform: uppercase;">
                                {{ $status->name }}
                            </span>
                            <span class="kanban-count-badge" data-status-id="{{ $status->id }}"
                                style="background: #{{ $columnColor }}22; color: #{{ $columnColor }}; border: 1px solid #{{ $columnColor }}44;">
                                {{ $columnCount }}
                            </span>
                        </div>
                        {{-- Colored top stripe --}}
                        <div
                            style="width: 24px; height: 4px; border-radius: 2px; background: #{{ $columnColor }}; opacity: 0.7;">
                        </div>
                    </div>

                    {{-- Dropzone --}}
                    <div class="kanban-dropzone" data-status-id="{{ $status->id }}"
                        data-status-name="{{ $status->name }}" data-status-color="#{{ $columnColor }}"
                        style="min-height: 580px; border-radius: var(--radius-lg);">

                        @foreach ($tasks->where('status_id', $status->id) as $task)
                            @php
                                $pColor = match ($task->priority) {
                                    'Critical' => 'var(--danger)',
                                    'High' => '#f59e0b',
                                    default => 'var(--primary)',
                                };
                                $pBg = match ($task->priority) {
                                    'Critical' => 'rgba(239,68,68,0.1)',
                                    'High' => 'rgba(245,158,11,0.1)',
                                    default => 'rgba(99,102,241,0.1)',
                                };
                                $pBorder = match ($task->priority) {
                                    'Critical' => 'rgba(239,68,68,0.25)',
                                    'High' => 'rgba(245,158,11,0.25)',
                                    default => 'rgba(99,102,241,0.25)',
                                };
                                $pIcon = match ($task->priority) {
                                    'Critical' => 'fa-fire-alt',
                                    'High' => 'fa-arrow-up',
                                    default => 'fa-minus',
                                };
                                $totalSubs = $task->subtasks->count();
                                $doneSubs = $task->subtasks
                                    ->filter(fn($s) => $s->status && $s->status->is_completed)
                                    ->count();
                                $hasDeadline = $task->deadline;
                                $isOverdue = $hasDeadline && $task->deadline->isPast();
                                $timeSpent = round($task->timeLogs->sum('duration') / 3600, 1);
                            @endphp

                            <div class="kanban-card" data-id="{{ $task->id }}"
                                data-status-id="{{ $status->id }}" wire:key="task-{{ $task->id }}"
                                style="border-top: 2px solid #{{ $columnColor }};">

                                {{-- Card Top Row --}}
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="kcard-badge"
                                        style="background: {{ $pBg }}; color: {{ $pColor }}; border: 1px solid {{ $pBorder }};">
                                        <i class="fas {{ $pIcon }}" style="font-size: 0.6rem;"></i>
                                        {{ $task->priority ?? 'Medium' }}
                                    </span>
                                    <div class="d-flex align-items-center gap-1">
                                        <div class="drag-handle" title="Drag to move">
                                            <i class="fas fa-grip-vertical"></i>
                                        </div>
                                        <div class="dropdown">
                                            <button class="kcard-menu-btn" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-premium"
                                                style="min-width: 140px;">
                                                <li>
                                                    <a class="dropdown-item small"
                                                        href="{{ route('details', $task->id) }}">
                                                        <i class="fas fa-eye me-2"
                                                            style="color: var(--primary);"></i>View
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item small"
                                                        href="{{ route('edit', $task->id) }}">
                                                        <i class="fas fa-edit me-2"
                                                            style="color: var(--accent);"></i>Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider"
                                                        style="border-color: var(--border-subtle); margin: 4px 0;">
                                                </li>
                                                @foreach ($statuses as $targetStatus)
                                                    @if ($targetStatus->id !== $status->id)
                                                        <li>
                                                            <button class="dropdown-item small text-low"
                                                                onclick="moveCardViaMenu({{ $task->id }}, {{ $targetStatus->id }}, '{{ $targetStatus->name }}')">
                                                                <span
                                                                    style="width: 8px; height: 8px; border-radius: 50%; background: {{ $targetStatus->color ?? '#94a3b8' }}; display: inline-block; margin-right: 8px;"></span>
                                                                Move to {{ $targetStatus->name }}
                                                            </button>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                {{-- Title --}}
                                <a href="{{ route('details', $task->id) }}"
                                    class="text-decoration-none d-block mb-2 fw-semibold lh-base"
                                    style="color: var(--text-high); font-size: 0.88rem;">
                                    {{ Str::limit($task->title, 65) }}
                                </a>

                                {{-- Tags --}}
                                @if ($task->tags->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1 mb-3">
                                        @foreach ($task->tags->take(3) as $tag)
                                            <span class="kcard-tag"
                                                style="background: {{ $tag->color }}1a; color: {{ $tag->color }}; border: 1px solid {{ $tag->color }}44;">
                                                {{ $tag->name }}
                                            </span>
                                        @endforeach
                                        @if ($task->tags->count() > 3)
                                            <span class="kcard-tag"
                                                style="background: var(--bg-input); color: var(--text-low);">+{{ $task->tags->count() - 3 }}</span>
                                        @endif
                                    </div>
                                @endif

                                {{-- Progress Bar (if subtasks) --}}
                                @if ($totalSubs > 0)
                                    @php $pct = round(($doneSubs / $totalSubs) * 100); @endphp
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1"
                                            style="font-size: 0.68rem; color: var(--text-low);">
                                            <span>{{ $doneSubs }}/{{ $totalSubs }} subtasks</span>
                                            <span>{{ $pct }}%</span>
                                        </div>
                                        <div
                                            style="height: 3px; background: var(--bg-input); border-radius: 2px; overflow: hidden;">
                                            <div
                                                style="height: 100%; width: {{ $pct }}%; background: #{{ $columnColor }}; border-radius: 2px; transition: width 0.5s;">
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Card Footer --}}
                                <div class="d-flex justify-content-between align-items-center mt-auto pt-2"
                                    style="border-top: 1px solid var(--border-subtle);">

                                    {{-- Assignee --}}
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($task->assignedTo)
                                            <div class="avatar-premium"
                                                style="width: 22px; height: 22px; font-size: 0.55rem; border: 1.5px solid var(--border-main);"
                                                title="{{ $task->assignedTo->name }}">
                                                @if ($task->assignedTo->profile_image)
                                                    <img src="{{ asset('storage/' . $task->assignedTo->profile_image) }}"
                                                        alt="">
                                                @else
                                                    {{ substr($task->assignedTo->name, 0, 1) }}
                                                @endif
                                            </div>
                                            <span
                                                style="font-size: 0.7rem; color: var(--text-low);">{{ explode(' ', $task->assignedTo->name)[0] }}</span>
                                        @else
                                            <div
                                                style="font-size: 0.68rem; color: var(--text-low); font-style: italic;">
                                                Unassigned</div>
                                        @endif
                                    </div>

                                    {{-- Meta chips --}}
                                    <div class="d-flex align-items-center gap-2"
                                        style="font-size: 0.68rem; color: var(--text-low);">
                                        @if ($timeSpent > 0)
                                            <span title="Time logged"><i
                                                    class="fas fa-clock me-1"></i>{{ $timeSpent }}h</span>
                                        @endif
                                        @if ($task->attachments->count() > 0)
                                            <span title="Attachments"><i
                                                    class="fas fa-paperclip me-1"></i>{{ $task->attachments->count() }}</span>
                                        @endif
                                        @if ($hasDeadline)
                                            <span title="{{ $isOverdue ? 'Overdue!' : 'Deadline' }}"
                                                style="color: {{ $isOverdue ? 'var(--danger)' : 'var(--text-low)' }};">
                                                <i
                                                    class="fas fa-calendar{{ $isOverdue ? '-times' : '-alt' }} me-1"></i>
                                                {{ $task->deadline->format('M d') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        {{-- Empty state --}}
                        @if ($tasks->where('status_id', $status->id)->isEmpty())
                            <div class="kanban-empty-state">
                                <div style="font-size: 1.8rem; margin-bottom: 8px; opacity: 0.2;">⬡</div>
                                <div style="font-size: 0.75rem; color: var(--text-low);">Drop tasks here</div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>{{-- /.kanban-wrapper --}}
</div>{{-- /single Livewire root --}}

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        let _sortables = [];
        let _isDragging = false;

        function destroySortables() {
            if (_isDragging) return; // Never destroy while a drag is in progress
            _sortables.forEach(s => {
                try {
                    s.destroy();
                } catch (e) {}
            });
            _sortables = [];
        }

        function initKanban() {
            destroySortables();
            const zones = document.querySelectorAll('.kanban-dropzone');
            if (!zones.length) return;

            zones.forEach(zone => {
                const inst = new Sortable(zone, {
                    group: 'kanban', // same group = cross-column DnD
                    animation: 180,
                    easing: 'cubic-bezier(0.25, 1, 0.5, 1)',
                    ghostClass: 'kanban-ghost',
                    chosenClass: 'kanban-chosen',
                    dragClass: 'kanban-dragging',
                    // No `handle` — entire card is draggable
                    filter: 'a, button, .dropdown-menu, .dropdown-item, select, input',
                    preventOnFilter: true,
                    fallbackOnBody: true,
                    swapThreshold: 0.65,
                    scroll: true,
                    scrollSensitivity: 60,
                    scrollSpeed: 12,

                    onStart(evt) {
                        _isDragging = true;
                        document.body.classList.add('is-dragging');
                        document.querySelectorAll('.kanban-dropzone').forEach(z => {
                            if (z !== evt.from) z.classList.add('drop-target');
                        });
                    },

                    onMove(evt) {
                        document.querySelectorAll('.kanban-dropzone').forEach(z => z.classList.remove(
                            'drop-active'));
                        if (evt.to) evt.to.classList.add('drop-active');
                        return true;
                    },

                    onEnd(evt) {
                        _isDragging = false;
                        document.body.classList.remove('is-dragging');
                        document.querySelectorAll('.kanban-dropzone').forEach(z => {
                            z.classList.remove('drop-target', 'drop-active');
                        });

                        const taskId = evt.item.getAttribute('data-id');
                        const newStatusId = evt.to ? evt.to.getAttribute('data-status-id') : null;
                        const oldStatusId = evt.from ? evt.from.getAttribute('data-status-id') : null;
                        const statusName = evt.to ? evt.to.getAttribute('data-status-name') : '';
                        const statusColor = evt.to ? evt.to.getAttribute('data-status-color') : null;

                        if (!taskId || !newStatusId || newStatusId === oldStatusId) return;

                        // Update card top-border color immediately
                        if (statusColor) evt.item.style.borderTopColor = statusColor;

                        // Update count badges immediately
                        changeBadge(oldStatusId, -1);
                        changeBadge(newStatusId, 1);

                        // Call Livewire — use the component id to find the right instance
                        const component = window.__kanbanLivewireId ?
                            Livewire.find(window.__kanbanLivewireId) :
                            null;

                        if (component) {
                            component.call('updateTaskStatus', taskId, newStatusId);
                        } else {
                            // Fallback: dispatch a custom event the component can listen to
                            window.dispatchEvent(new CustomEvent('kanban-move', {
                                detail: {
                                    taskId,
                                    newStatusId
                                }
                            }));
                            // Also try @this which Blade compiles at render-time
                            try {
                                @this.updateTaskStatus(taskId, newStatusId);
                            } catch (e) {}
                        }

                        showToast('Moved to <strong>' + statusName + '</strong>', statusColor);
                    },
                });

                _sortables.push(inst);
            });
        }

        function changeBadge(statusId, delta) {
            if (!statusId) return;
            const badge = document.querySelector('.kanban-count-badge[data-status-id="' + statusId + '"]');
            if (!badge) return;
            const n = Math.max(0, (parseInt(badge.textContent) || 0) + delta);
            badge.textContent = n;
            const zone = document.querySelector('.kanban-dropzone[data-status-id="' + statusId + '"]');
            if (zone) {
                const empty = zone.querySelector('.kanban-empty-state');
                if (empty) empty.style.display = zone.querySelectorAll('.kanban-card').length === 0 ? 'flex' : 'none';
            }
        }

        function showToast(html, color) {
            const t = document.getElementById('dnd-toast');
            if (!t) return;
            const m = document.getElementById('dnd-toast-msg');
            if (m) m.innerHTML = html;
            const ic = t.querySelector('i');
            if (ic) ic.style.color = color || 'var(--accent)';
            t.style.display = 'flex';
            t.classList.add('show');
            clearTimeout(t._tmr);
            t._tmr = setTimeout(() => {
                t.classList.remove('show');
                setTimeout(() => {
                    t.style.display = 'none';
                }, 300);
            }, 2600);
        }

        // Store the Livewire component id at render-time
        window.__kanbanLivewireId = '{{ $this->getId() }}';

        // Expose menu-based move
        window.moveCardViaMenu = function(taskId, statusId, statusName) {
            try {
                @this.updateTaskStatus(taskId, statusId);
            } catch (e) {
                const c = Livewire.find(window.__kanbanLivewireId);
                if (c) c.call('updateTaskStatus', taskId, statusId);
            }
            showToast('Moved to <strong>' + statusName + '</strong>');
        };

        // Initialise
        document.addEventListener('DOMContentLoaded', initKanban);
        document.addEventListener('livewire:navigated', initKanban);

        // After Livewire morphs the DOM, wait briefly then re-init (but never during a drag)
        document.addEventListener('livewire:morph-complete', () => {
            if (!_isDragging) setTimeout(initKanban, 150);
        });

        // Fallback: also listen to the older event name
        document.addEventListener('livewire:update', () => {
            if (!_isDragging) setTimeout(initKanban, 150);
        });
    </script>

    <style>
        /* ─── Kanban Layout ─── */
        user-select: none;
        }

        .kanban-status-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .kanban-count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            border-radius: 999px;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            transition: all 0.2s;
        }

        /* ─── Drop Zone ─── */
        .kanban-dropzone {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 8px;
            border-radius: var(--radius-lg);
            border: 2px dashed var(--border-main);
            background: transparent;
            transition: background 0.2s, border-color 0.2s, box-shadow 0.15s;
            min-height: 580px;
        }

        .kanban-dropzone.drop-target {
            background: rgba(var(--primary-rgb), 0.04);
            border-color: rgba(var(--primary-rgb), 0.3);
        }

        .kanban-dropzone.drop-active {
            background: rgba(var(--primary-rgb), 0.08);
            border-color: var(--primary);
            box-shadow: inset 0 0 0 2px rgba(var(--primary-rgb), 0.15);
        }

        /* ─── Kanban Card ─── */
        .kanban-card {
            background: var(--bg-surface);
            border-radius: var(--radius-md);
            padding: 14px;
            border: 1px solid var(--border-subtle);
            cursor: default;
            transition: transform 0.18s cubic-bezier(0.4, 0, 0.2, 1),
                box-shadow 0.18s cubic-bezier(0.4, 0, 0.2, 1),
                border-color 0.2s;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .kanban-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent 60%, rgba(255, 255, 255, 0.015));
            pointer-events: none;
        }

        .kanban-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px -8px rgba(0, 0, 0, 0.25);
            border-color: var(--border-main);
            z-index: 2;
        }

        /* ─── Drag States ─── */
        .kanban-ghost {
            opacity: 0;
            background: transparent !important;
        }

        .kanban-chosen {
            opacity: 1;
        }

        .kanban-dragging {
            transform: rotate(1.5deg) scale(1.03) !important;
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.4) !important;
            opacity: 0.95 !important;
            cursor: grabbing !important;
            z-index: 9999 !important;
        }

        body.is-dragging * {
            cursor: grabbing !important;
        }

        /* ─── Drag Handle ─── */
        .drag-handle {
            cursor: grab;
            color: var(--text-low);
            opacity: 0;
            font-size: 0.75rem;
            padding: 2px 4px;
            border-radius: 4px;
            transition: opacity 0.15s, background 0.15s;
            display: flex;
            align-items: center;
        }

        .kanban-card:hover .drag-handle {
            opacity: 1;
        }

        .drag-handle:hover {
            background: var(--bg-input);
            color: var(--text-medium);
        }

        /* ─── Card Sub-elements ─── */
        .kcard-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 0.68rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .kcard-tag {
            display: inline-flex;
            align-items: center;
            padding: 1px 7px;
            border-radius: 999px;
            font-size: 0.65rem;
            font-weight: 500;
            white-space: nowrap;
        }

        .kcard-menu-btn {
            background: none;
            border: none;
            color: var(--text-low);
            opacity: 0;
            font-size: 0.75rem;
            cursor: pointer;
            padding: 3px 5px;
            border-radius: var(--radius-sm);
            transition: opacity 0.15s, background 0.15s;
            display: flex;
            align-items: center;
        }

        .kanban-card:hover .kcard-menu-btn {
            opacity: 1;
        }

        .kcard-menu-btn:hover {
            background: var(--bg-input);
            color: var(--text-high);
        }

        /* ─── Empty State ─── */
        .kanban-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            flex: 1;
            pointer-events: none;
        }

        .kanban-dropzone:not(:has(.kanban-card)) .kanban-empty-state {
            display: flex;
        }

        /* ─── Toast ─── */
        .dnd-toast {
            position: fixed;
            bottom: 28px;
            left: 50%;
            transform: translateX(-50%) translateY(20px);
            background: var(--bg-surface);
            border: 1px solid var(--border-main);
            backdrop-filter: blur(16px);
            border-radius: var(--radius-full);
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            color: var(--text-high);
            box-shadow: 0 8px 32px -8px rgba(0, 0, 0, 0.4);
            z-index: 99999;
            opacity: 0;
            transition: opacity 0.25s, transform 0.25s;
            pointer-events: none;
        }

        .dnd-toast.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        /* ─── Column Header ─── */
        .kanban-col-header {
            padding-bottom: 4px;
        }

        /* ─── Responsive ─── */
        @media (max-width: 768px) {
            .kanban-card {
                padding: 10px;
            }
        }
    </style>
@endpush
