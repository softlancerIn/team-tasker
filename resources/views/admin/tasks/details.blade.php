<x-admin>
    <x-slot:title>
        Task Details | Team Tasker
    </x-slot:title>

    <div class="top-bar">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h3 class="mb-0">Task Details</h3>
        </div>
        <div class="d-flex gap-2">
            @if ($activeTimer)
                <form action="{{ route('tasks.stop_timer', $task->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-stop me-2"></i> Stop Timer
                        <span class="badge bg-white bg-opacity-20 ms-2" id="liveTimer">00:00:00</span>
                    </button>
                    <input type="hidden" id="startTimeValue" value="{{ $activeTimer->start_time->toIso8601String() }}">
                </form>
            @else
                <form action="{{ route('tasks.start_timer', $task->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-play me-2"></i> Start Timer
                    </button>
                </form>
            @endif

            <a href="{{ route('edit', $task->id) }}" class="btn btn-outline-primary">
                <i class="fas fa-edit me-2"></i> Edit
            </a>
            <a href="{{ route('delete', $task->id) }}" class="btn btn-outline-danger"
                onclick="return confirm('Are you sure?')">
                <i class="fas fa-trash-alt"></i>
            </a>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-8">
            <div class="glass-card mb-4">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <h2 class="mb-0">{{ $task->title }}</h2>
                    @if ($task->status == 'completed')
                        <span
                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">Completed</span>
                    @elseif($task->status == 'in_progress')
                        <span
                            class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill">In
                            Progress</span>
                    @else
                        <span
                            class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2 rounded-pill">Pending</span>
                    @endif
                </div>

                <h6 class="text-muted uppercase extra-small mb-3">Description</h6>
                <div class="text-white-50 lh-lg mb-5 ck-content" style="white-space: pre-wrap;">{!! $task->description !!}
                </div>

                <div class="mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-muted uppercase extra-small mb-0">Progress</h6>
                        <span class="text-white small fw-bold">{{ $task->progress }}%</span>
                    </div>
                    <div class="progress bg-white bg-opacity-10" style="height: 8px; border-radius: 4px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                            role="progressbar" style="width: {{ $task->progress }}%;"
                            aria-valuenow="{{ $task->progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <hr class="border-secondary border-opacity-25 mb-4">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">Activity Feed</h5>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse"
                        data-bs-target="#addLogForm">
                        <i class="fas fa-plus me-1"></i> Add Note
                    </button>
                </div>

                <div class="collapse mb-4" id="addLogForm">
                    <form action="{{ route('tasks.log', $task->id) }}" method="POST"
                        class="bg-opacity-5 p-3 rounded-3 border border-secondary border-opacity-25">
                        @csrf
                        <div class="mb-3">
                            <textarea id="longtextarea" name="note" class="form-control" rows="3" placeholder="Write a log note..."></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-sm px-4">Save Note</button>
                        </div>
                    </form>
                </div>

                <div class="mb-4">
                    <ul class="nav nav-pills glass-pills gap-2" id="taskTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="activity-tab" data-bs-toggle="tab"
                                data-bs-target="#activity" type="button" role="tab">
                                <i class="fas fa-stream me-2"></i>Activity Feed
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="timeline-tab" data-bs-toggle="tab" data-bs-target="#timeline"
                                type="button" role="tab">
                                <i class="fas fa-history me-2"></i>Timeline
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="tab-content" id="taskTabsContent">
                    <!-- Activity Feed Tab -->
                    <div class="tab-pane fade show active" id="activity" role="tabpanel">
                        <div class="activity-timeline">
                            @forelse($task->logs as $log)
                                <div class="d-flex gap-3 mb-4">
                                    <div class="position-relative">
                                        <div class="avatar"
                                            style="width: 32px; height: 32px; font-size: 0.8rem; background: {{ $log->type == 'message' ? 'rgba(34, 197, 94, 0.1)' : 'rgba(99, 102, 241, 0.1)' }}; color: {{ $log->type == 'message' ? '#22c55e' : 'var(--primary)' }};">
                                            {{ substr($log->user->name, 0, 1) }}
                                        </div>
                                        @if (!$loop->last)
                                            <div class="position-absolute start-50 top-100 border-start border-secondary border-opacity-25"
                                                style="height: 20px; transform: translateX(-50%);"></div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-medium text-white small">{{ $log->user->name }}</span>
                                            <span
                                                class="text-muted extra-small">{{ $log->created_at->diffForHumans() }}</span>
                                        </div>
                                        @if ($log->type == 'message')
                                            <span
                                                class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 extra-small mb-1">Admin
                                                Message</span>
                                        @endif
                                        <div class="text-white-50 small ck-content">{!! $log->note !!}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted small">
                                    No activity logged yet.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Timeline Tab -->
                    <div class="tab-pane fade" id="timeline" role="tabpanel">
                        <div class="timeline-logs">
                            @php $totalSeconds = 0; @endphp
                            @forelse($task->timeLogs as $timeLog)
                                @php $totalSeconds += $timeLog->duration; @endphp
                                <div class="glass-card mb-3 p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                {{ substr($timeLog->user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="text-white small fw-bold">{{ $timeLog->user->name }}</div>
                                                <div class="text-muted extra-small">
                                                    {{ $timeLog->start_time->format('M d, H:i') }} —
                                                    {{ $timeLog->end_time ? $timeLog->end_time->format('H:i') : 'In Progress' }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="text-primary small fw-bold">
                                                {{ $timeLog->end_time ? gmdate('H:i:s', $timeLog->duration) : 'Active' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted small">
                                    No time logged yet.
                                </div>
                            @endforelse

                            @if ($totalSeconds > 0)
                                <div class="alert alert-primary bg-opacity-5 border-primary border-opacity-25 mt-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-white small">Total Time Invested</span>
                                        <span class="text-primary fw-bold">{{ floor($totalSeconds / 3600) }}h
                                            {{ floor(($totalSeconds % 3600) / 60) }}m {{ $totalSeconds % 60 }}s</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="glass-card mb-4">
                <h5 class="mb-4">Meta Information</h5>
                <div class="mb-3">
                    <div class="text-muted small">Created at</div>
                    <div class="text-white small">{{ $task->created_at->format('M d, Y h:i A') }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Last updated</div>
                    <div class="text-white small">{{ $task->updated_at->format('M d, Y h:i A') }}</div>
                </div>
                <div class="mb-0">
                    <div class="text-muted small">Owner</div>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <div class="avatar" style="width: 24px; height: 24px; font-size: 0.6rem;">
                            {{ substr(Auth::user()->name, 0, 1) }}</div>
                        <span class="text-white small">{{ Auth::user()->name }}</span>
                    </div>
                </div>
            </div>

            <div class="glass-card mb-4">
                <h5 class="mb-4">Update Progress</h5>
                <form action="{{ route('tasks.progress', $task->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-muted small">Task Status</label>
                        <select name="status"
                            class="form-select bg-dark text-white border-secondary border-opacity-25">
                            <option value="pending" {{ $task->status == 'pending' ? 'selected' : '' }}>Pending
                            </option>
                            <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>In
                                Progress</option>
                            <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>Completed
                            </option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <label class="form-label text-muted small mb-0">Completion</label>
                            <span class="text-primary small fw-bold" id="progressValue">{{ $task->progress }}%</span>
                        </div>
                        <input type="range" name="progress" class="form-range" min="0" max="100"
                            step="5" value="{{ $task->progress }}"
                            oninput="document.getElementById('progressValue').innerText = this.value + '%'">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-sm">Update Progress</button>
                    </div>
                </form>
            </div>

            <div class="glass-card">
                <h5 class="mb-3">Message Admin</h5>
                <p class="text-muted small mb-4">Send a direct message to the administrator regarding this task.</p>
                <form action="{{ route('tasks.message', $task->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <textarea id="mytextarea" name="message" class="form-control" rows="4"
                            placeholder="Type your message here..."></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i> Send Message
                        </button>
                    </div>
                </form>
            </div>
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

        .glass-pills .nav-link.active {
            background: var(--primary) !important;
            color: white !important;
        }

        .glass-pills .nav-link:not(.active):hover {
            background: rgba(255, 255, 255, 0.08);
            color: white;
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

        tinymce.init({
            selector: '#mytextarea,#longtextarea',
            height: 300,
            skin: 'oxide-dark',
            content_css: 'dark',
            padding: 0,
            branding: false,
            placeholder: 'Start typing your message here...',
            plugins: [
                'advlist', 'autolink', 'link', 'image', 'lists', 'charmap', 'preview', 'anchor', 'pagebreak',
                'searchreplace', 'wordcount', 'visualblocks', 'visualchars', 'code', 'fullscreen',
                'insertdatetime',
                'media', 'table', 'emoticons', 'help'
            ],
            menubar: false,
            toolbar: 'undo redo | bold italic | bullist numlist | link | code',
            extended_valid_elements: 'i[class|style],table[class|style],th[class|style],td[class|style],h1[class|style],h2[class|style],h3[class|style],h4[class|style],h5[class|style],h6[class|style]',
            valid_elements: '*[*]',
            content_css: false,
            content_style: 'body { background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.05), transparent), #1a2436; color: rgba(255, 255, 255, 0.8); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; } i { font-style: italic; } body.mce-content-body[data-mce-placeholder]:not(.mce-visualblocks)::before { color: rgba(255, 255, 255, 0.4); }',
            entity_encoding: 'raw',
            remove_trailing_brs: false,
            valid_children: '+body[style|i]',
            setup: function(editor) {
                editor.on('init', function() {
                    const container = editor.getContainer();
                    if (container) {
                        container.style.border = '1px solid rgba(99, 102, 241, 0.3)';
                        container.style.borderRadius = '8px';
                    }
                });
            }
        });

        feather.replace()
    </script>
</x-admin>
