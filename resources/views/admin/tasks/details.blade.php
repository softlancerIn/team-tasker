<x-admin>
    <x-slot:title>
        Task Details | Team Tasker
    </x-slot:title>

    <div class="top-bar-premium">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('index') }}" class="btn-premium btn-premium-secondary btn-sm p-0 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px; border-radius: 50%;">
                <i class="fas fa-arrow-left" style="font-size: 0.9rem;"></i>
            </a>
            <div>
                <h1 class="h3 fw-semibold mb-1 text-high">Task Details</h1>
                <p class="text-low mb-0" style="font-size: 0.9rem;">View and manage task information.</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            @if ($activeTimer)
                <form action="{{ route('tasks.stop_timer', $task->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn-premium py-2 px-4 shadow-sm"
                        style="background: rgba(var(--danger-rgb), 0.1); color: var(--danger); border: 1px solid rgba(var(--danger-rgb), 0.2);">
                        <i class="fas fa-stop me-2"></i> Stop Timer
                        <span class="badge-premium ms-2" id="liveTimer"
                            style="background: rgba(var(--danger-rgb), 0.1); color: var(--danger);">00:00:00</span>
                    </button>
                    <input type="hidden" id="startTimeValue" value="{{ $activeTimer->start_time->toIso8601String() }}">
                </form>
            @else
                <form action="{{ route('tasks.start_timer', $task->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn-premium btn-premium-primary py-2 px-4 shadow-sm">
                        <i class="fas fa-play me-2"></i> Start Timer
                    </button>
                </form>
            @endif

            <a href="{{ route('edit', $task->id) }}" class="btn-premium btn-premium-secondary py-2 px-4">
                <i class="fas fa-edit me-2"></i> Edit
            </a>
            <a href="{{ route('delete', $task->id) }}" class="btn-premium py-2 px-3 shadow-sm"
                style="background: rgba(var(--danger-rgb), 0.1); color: var(--danger); border: 1px solid rgba(var(--danger-rgb), 0.2);"
                onclick="return confirm('Are you sure?')">
                <i class="fas fa-trash-alt"></i>
            </a>
        </div>
    </div>

    <div class="row g-4 mt-2">
        <div class="col-lg-8">
            <div class="data-grid-wrapper p-4 mb-4" style="background: var(--bg-surface); border: 1px solid var(--border-main); border-radius: var(--radius-lg);">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <h2 class="mb-0 fw-bold text-high">{{ $task->title }}</h2>
                    @if ($task->status)
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
                            {{ $task->status->name }}
                        </span>
                    @else
                        <span class="badge-premium"
                            style="background: var(--bg-input); color: var(--text-low);">Unknown</span>
                    @endif
                </div>

                <div class="d-flex flex-wrap gap-2 mb-4">
                    @if ($task->priority)
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
                            style="background: {{ $pBg }}; color: {{ $pColor }}; border: 1px solid {{ $pBorder }};">
                            <i class="fas fa-flag me-1"></i> {{ $task->priority }} Priority
                        </span>
                    @endif
                    @foreach ($task->tags as $tag)
                        <span class="badge-premium"
                            style="background: {{ $tag->color }}1a; color: {{ $tag->color }}; border: 1px solid {{ $tag->color }}40;">
                            <i class="fas fa-tag me-1"></i> {{ $tag->name }}
                        </span>
                    @endforeach
                    @if ($task->is_recurring)
                        <span class="badge-premium"
                            style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary); border: 1px solid rgba(var(--primary-rgb), 0.2);">
                            <i class="fas fa-redo me-1"></i> Recurring ({{ ucfirst($task->recurring_interval) }})
                        </span>
                    @endif
                </div>

                <h6 class="text-muted uppercase extra-small mb-3">Description</h6>
                <div class="text-main-50 lh-lg mb-5 ck-content" style="white-space: pre-wrap;">{!! $task->description !!}
                </div>

                <div class="row mb-5 g-4">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="heading-label mb-0">Progress</h6>
                            <span class="text-high small fw-bold">{{ $task->progress }}%</span>
                        </div>
                        <div class="progress" style="height: 8px; border-radius: 4px; background: var(--bg-input);">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                role="progressbar" style="width: {{ $task->progress }}%;"
                                aria-valuenow="{{ $task->progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        @php
                            $actualHours = round($task->timeLogs()->sum('duration') / 3600, 2);
                            $overEstimated = $task->estimated_hours && $actualHours > $task->estimated_hours;
                        @endphp
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="heading-label mb-0">Time Health</h6>
                            <span class="small fw-bold {{ $overEstimated ? 'text-danger' : 'text-high' }}">
                                {{ $actualHours }}h / {{ $task->estimated_hours ?? '?' }}h
                            </span>
                        </div>
                        <div class="progress" style="height: 8px; border-radius: 4px; background: var(--bg-input);">
                            <div class="progress-bar bg-{{ $overEstimated ? 'danger' : 'success' }}" role="progressbar"
                                style="width: {{ $task->estimated_hours ? min(100, ($actualHours / $task->estimated_hours) * 100) : 0 }}%;">
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="border-secondary border-opacity-25 mb-4">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">Collaboration & Data</h5>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse"
                        data-bs-target="#addLogForm">
                        <i class="fas fa-plus me-1"></i> Add Note
                    </button>
                </div>

                <div class="collapse mb-4" id="addLogForm">
                    <form action="{{ route('tasks.log', $task->id) }}" method="POST"
                        class="bg-surface p-4 rounded-xl border border-main shadow-sm">
                        @csrf
                        <div class="mb-4">
                            <x-textarea id="longtextarea" name="note" class="form-premium-control" rows="3"
                                placeholder="Write a note..." texteditor="true"></x-textarea>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="publicMessage" name="type"
                                    value="message">
                                <label class="form-check-label text-medium small" for="publicMessage">Visible to
                                    Client</label>
                            </div>
                            <button type="submit" class="btn-premium btn-premium-primary py-2 px-4">Save
                                Note</button>
                        </div>
                    </form>
                </div>

                <div class="mb-4">
                    <ul class="nav nav-pills glass-pills gap-2" id="taskTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="activity-tab" data-bs-toggle="tab"
                                data-bs-target="#activity" type="button" role="tab">
                                <i class="fas fa-stream me-2"></i>Activity
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="subtasks-tab" data-bs-toggle="tab"
                                data-bs-target="#subtasks" type="button" role="tab">
                                <i class="fas fa-tasks me-2"></i>Subtasks ({{ $task->subtasks->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="timeline-tab" data-bs-toggle="tab"
                                data-bs-target="#timeline" type="button" role="tab">
                                <i class="fas fa-history me-2"></i>Logs
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="files-tab" data-bs-toggle="tab" data-bs-target="#files"
                                type="button" role="tab">
                                <i class="fas fa-paperclip me-2"></i>Files ({{ $task->attachments->count() }})
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="tab-content" id="taskTabsContent">
                    <!-- Activity Feed Tab -->
                    <div class="tab-pane fade show active" id="activity" role="tabpanel">
                        <livewire:task-activity-tab :taskId="$task->id" />
                    </div>

                    <!-- Subtasks Tab -->
                    <div class="tab-pane fade" id="subtasks" role="tabpanel">
                        <div class="glass-card p-0 overflow-hidden border-0">
                            <ul class="list-group list-group-flush bg-transparent">
                                @forelse($task->subtasks as $subtask)
                                    <li
                                        class="list-group-item bg-transparent border-secondary border-opacity-10 py-3 px-4 d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-3">
                                            <i class="fas fa-tasks text-muted small"></i>
                                            <div>
                                                <a href="{{ route('show', $subtask->id) }}"
                                                    class="text-main fw-medium text-decoration-none">
                                                    {{ $subtask->title }}
                                                </a>
                                                <div class="extra-small text-muted mt-1">
                                                    Assigned to: {{ $subtask->assignedTo->name ?? 'Unassigned' }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="badge-premium"
                                                style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary); border: 1px solid rgba(var(--primary-rgb), 0.2); font-size: 0.7rem;">
                                                {{ $subtask->status->name ?? 'Unknown' }}
                                            </span>
                                            <span class="text-high small fw-bold">{{ $subtask->progress }}%</span>
                                        </div>
                                    </li>
                                @empty
                                    <li class="list-group-item bg-transparent text-center py-5 text-muted small">
                                        No subtasks found for this task.
                                    </li>
                                @endforelse
                                <li class="list-group-item bg-transparent py-3 px-4 text-center">
                                    <a href="{{ route('create') }}?parent_id={{ $task->id }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-plus me-1"></i> Create Subtask
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Timeline Tab -->
                    <div class="tab-pane fade" id="timeline" role="tabpanel">
                        <livewire:task-time-log-tab :taskId="$task->id" />
                    </div>

                    <!-- Files Tab -->
                    <div class="tab-pane fade" id="files" role="tabpanel">
                        <div class="row g-3">
                            @forelse($task->attachments as $attachment)
                                <div class="col-md-6 col-xl-4">
                                    <div class="p-3 rounded-3"
                                        style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color);">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="stat-icon icon-primary mb-0"
                                                style="width: 40px; height: 40px; font-size: 1rem;">
                                                <i class="fas fa-file"></i>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="text-main small fw-medium text-truncate"
                                                    title="{{ $attachment->file_name }}">
                                                    {{ $attachment->file_name }}
                                                </div>
                                                <div class="extra-small text-muted">
                                                    {{ round($attachment->file_size / 1024, 1) }} KB •
                                                    {{ $attachment->user->name }}
                                                </div>
                                            </div>
                                            <a href="{{ asset('storage/' . $attachment->file_path) }}"
                                                target="_blank" class="btn btn-sm btn-outline-primary border-0">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5 text-muted small">
                                    No attachments uploaded yet.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="data-grid-wrapper p-4 mb-4" style="background: var(--bg-surface); border: 1px solid var(--border-main); border-radius: var(--radius-lg);">
                <h5 class="mb-4">Dependencies</h5>
                @forelse($task->dependencies as $dep)
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="stat-icon icon-warning mb-0"
                            style="width: 32px; height: 32px; font-size: 0.8rem; background: rgba(245, 158, 11, 0.1);">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="overflow-hidden">
                            <div class="extra-small text-muted">Blocks this task</div>
                            <a href="{{ route('show', $dep->blocker->id) }}"
                                class="text-main small fw-medium text-decoration-none text-truncate d-block">
                                {{ $dep->blocker->title }}
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-muted small">No blocking dependencies.</div>
                @endforelse
            </div>

            <div class="data-grid-wrapper p-4 mb-4" style="background: var(--bg-surface); border: 1px solid var(--border-main); border-radius: var(--radius-lg);">
                <h5 class="mb-4">Meta Information</h5>
                <div class="mb-3">
                    <div class="text-muted small">Created at</div>
                    <div class="text-main small">{{ $task->created_at->format('M d, Y h:i A') }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Last updated</div>
                    <div class="text-main small">{{ $task->updated_at->format('M d, Y h:i A') }}</div>
                </div>
                <div class="mb-0">
                    <div class="text-muted small">Owner</div>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <div class="avatar" style="width: 24px; height: 24px; font-size: 0.6rem;">
                            {{ substr(Auth::user()->name, 0, 1) }}</div>
                        <span class="text-main small">{{ Auth::user()->name }}</span>
                    </div>
                </div>
            </div>

            <div class="data-grid-wrapper p-4 mb-4" style="background: var(--bg-surface); border: 1px solid var(--border-main); border-radius: var(--radius-lg);">
                <h5 class="fw-bold mb-4 text-high">Update Progress</h5>
                <form action="{{ route('tasks.progress', $task->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="heading-label d-block mb-3">Task Status</label>
                        <x-select name="status_id" placeholder="Select Status">
                            @foreach ($statuses as $status)
                                <option value="{{ $status->id }}"
                                    {{ isset($task->status_id) && $task->status_id == $status->id ? 'selected' : '' }}
                                    class="bg-dark">
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </x-select>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-3">
                            <label class="heading-label mb-0">Completion</label>
                            <span class="text-primary fw-bold" id="progressValue">{{ $task->progress }}%</span>
                        </div>
                        <input type="range" name="progress" class="form-range" min="0" max="100"
                            step="5" value="{{ $task->progress }}"
                            oninput="document.getElementById('progressValue').innerText = this.value + '%'">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn-premium btn-premium-primary py-2">Update Progress</button>
                    </div>
                </form>
            </div>

            {{-- <div class="glass-card">
                <h5 class="mb-3">Message Admin</h5>
                <p class="text-muted small mb-4">Send a direct message to the administrator regarding this task.</p>
                <form action="{{ route('tasks.message', $task->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <x-textarea id="mytextarea" name="message" class="form-control" rows="4"
                            placeholder="Type your message here..."></x-textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i> Send Message
                        </button>
                    </div>
                </form>
            </div> --}}
        </div>
    </div>

    <style>
        .glass-pills {
            background: rgba(255, 255, 255, 0.05);
            padding: 5px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .glass-pills .nav-link {
            color: rgba(255, 255, 255, 0.6);
            border-radius: 8px;
            padding: 8px 20px;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        [data-theme="light"] .glass-pills .nav-link {
            color: var(--text-muted);
        }

        [data-theme="light"] .glass-pills {
            background: rgba(0, 0, 0, 0.05);
            border-color: rgba(0, 0, 0, 0.1);
        }

        .glass-pills .nav-link.active {
            background: var(--primary) !important;
            color: white !important;
        }

        .glass-pills .nav-link:not(.active):hover {
            background: rgba(255, 255, 255, 0.08);
            color: white;
        }

        [data-theme="light"] .glass-pills .nav-link:not(.active):hover {
            background: rgba(0, 0, 0, 0.08);
            color: var(--primary);
        }

        /* Dark Mode Specific Fixes */
        [data-theme="dark"] .text-main-50 {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        [data-theme="dark"] .text-muted {
            color: rgba(255, 255, 255, 0.5) !important;
        }
    </style>

    <script>
        setTimeout(function() {
            $('.alert-success,.alert-danger').fadeOut('fast');
        }, 3000);

        // Live Timer Logic
        const startTimeStr = document.getElementById('startTimeValue')?.value;
        if (startTimeStr) {
            const startTime = new Date(startTimeStr);
            const liveTimer = document.getElementById('liveTimer');

            setInterval(() => {
                const now = new Date();
                const diff = Math.floor((now - startTime) / 1000);

                const h = String(Math.floor(diff / 3600)).padStart(2, '0');
                const m = String(Math.floor((diff % 3600) / 60)).padStart(2, '0');
                const s = String(diff % 60).padStart(2, '0');

                liveTimer.innerText = `${h}:${m}:${s}`;
            }, 1000);
        }


        feather.replace()
    </script>
</x-admin>


