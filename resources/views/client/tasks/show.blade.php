<x-client title="Task #{{ $task->id }}">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('client.dashboard') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
            <h2 class="h4 text-white mb-0">
                #{{ $task->id }} - {{ $task->title }}
            </h2>
        </div>
        <div class="d-flex gap-2">
            @if ($task->priority)
                <span
                    class="badge bg-{{ $task->priority == 'Critical' ? 'danger' : ($task->priority == 'High' ? 'warning' : 'info') }} bg-opacity-10 text-{{ $task->priority == 'Critical' ? 'danger' : ($task->priority == 'High' ? 'warning' : 'info') }} border border-opacity-25 px-3 py-2 rounded-pill extra-small">
                    <i class="fas fa-flag me-1"></i> {{ $task->priority }}
                </span>
            @endif
            <span
                class="badge bg-{{ $task->status->color ?? 'secondary' }} bg-opacity-10 text-{{ $task->status->color ?? 'secondary' }} border border-{{ $task->status->color ?? 'secondary' }} border-opacity-25 px-3 py-2 rounded-pill fs-6">
                {{ $task->status->name ?? 'Pending' }}
            </span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Task Progress -->
            <div class="glass-card mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-white small mb-0 uppercase extra-small">Completion Progress</h6>
                    <span class="text-primary fw-bold">{{ $task->progress }}%</span>
                </div>
                <div class="progress bg-white bg-opacity-10" style="height: 10px; border-radius: 5px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar"
                        style="width: {{ $task->progress }}%;" aria-valuenow="{{ $task->progress }}" aria-valuemin="0"
                        aria-valuemax="100"></div>
                </div>
            </div>

            <!-- Task Description -->
            <div class="glass-card mb-4">
                <h6 class="text-muted small uppercase extra-small mb-3">Task Description</h6>
                <div class="text-white-50 lh-base ck-content">
                    {!! $task->description !!}
                </div>
            </div>

            <!-- Tabs -->
            <div class="mb-4">
                <ul class="nav nav-pills glass-pills gap-2" id="taskTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active font-outfit" id="activity-tab" data-bs-toggle="tab"
                            data-bs-target="#activity" type="button" role="tab">
                            <i class="fas fa-stream me-2"></i>Activity
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link font-outfit" id="files-tab" data-bs-toggle="tab" data-bs-target="#files"
                            type="button" role="tab">
                            <i class="fas fa-paperclip me-2"></i>Files ({{ $task->attachments->count() }})
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
                                        style="width: 32px; height: 32px; font-size: 0.8rem; background: {{ $log->user_id == Auth::id() ? 'rgba(99, 102, 241, 0.1)' : 'rgba(34, 197, 94, 0.1)' }}; color: {{ $log->user_id == Auth::id() ? 'var(--primary)' : '#22c55e' }};">
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
                                    @if ($log->user->role_id != 3)
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 extra-small mb-1">
                                            <i class="fas fa-headset me-1"></i> Support Agent
                                        </span>
                                    @else
                                        <span
                                            class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 extra-small mb-1">
                                            <i class="fas fa-user me-1"></i> You
                                        </span>
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

                    <!-- Reply Form -->
                    <div class="glass-card mt-4 border-primary border-opacity-10">
                        <h6 class="text-white mb-3"><i class="fas fa-reply me-2 text-primary"></i>Send a Message</h6>
                        <form action="{{ route('client.tasks.reply', $task->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <textarea id="reply-editor" name="note" class="form-control" rows="4" placeholder="Type your message here..."></textarea>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-paper-plane me-1"></i> Send Reply
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Files Tab -->
                <div class="tab-pane fade" id="files" role="tabpanel">
                    <div class="row g-3">
                        @forelse($task->attachments as $attachment)
                            <div class="col-md-6">
                                <div class="glass-card p-3 h-100">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="stat-icon icon-primary mb-0"
                                            style="width: 40px; height: 40px; font-size: 1rem;">
                                            <i class="fas fa-file"></i>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="text-white small fw-medium text-truncate"
                                                title="{{ $attachment->file_name }}">
                                                {{ $attachment->file_name }}
                                            </div>
                                            <div class="extra-small text-muted">
                                                {{ round($attachment->file_size / 1024, 1) }} KB •
                                                {{ $attachment->user->name }}
                                            </div>
                                        </div>
                                        <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank"
                                            class="btn btn-sm btn-outline-primary border-0">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5 text-muted small glass-card">
                                <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                                <p>No attachments uploaded yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="glass-card sticky-top" style="top: 2rem;">
                <h6 class="text-white mb-4 uppercase extra-small border-bottom border-white border-opacity-10 pb-2">
                    Task Details</h6>

                <div class="mb-4">
                    <label class="text-muted extra-small d-block uppercase mb-1">Created At</label>
                    <span class="text-white small fw-medium">
                        <i class="far fa-calendar-alt me-2 text-primary"></i>{{ $task->created_at->format('M d, Y') }}
                    </span>
                </div>

                <div class="mb-4">
                    <label class="text-muted extra-small d-block uppercase mb-1">Deadline</label>
                    @if ($task->deadline)
                        <span class="text-white small fw-medium {{ $task->deadline->isPast() ? 'text-danger' : '' }}">
                            <i
                                class="far fa-clock me-2 text-{{ $task->deadline->isPast() ? 'danger' : 'primary' }}"></i>{{ $task->deadline->format('M d, Y') }}
                        </span>
                    @else
                        <span class="text-muted small italic">No deadline set</span>
                    @endif
                </div>

                <div class="mb-4">
                    <label class="text-muted extra-small d-block uppercase mb-1">Assigned Support</label>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        @if ($task->assignedTo)
                            <div class="avatar bg-primary text-white"
                                style="width: 32px; height: 32px; font-size: 0.8rem; border-radius: 50%;">
                                {{ substr($task->assignedTo->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="text-white small fw-medium">{{ $task->assignedTo->name }}</div>
                                <div class="extra-small text-muted">Technical Support</div>
                            </div>
                        @else
                            <div class="avatar bg-secondary text-white opacity-50"
                                style="width: 32px; height: 32px; font-size: 0.8rem; border-radius: 50%;">
                                <i class="fas fa-user-clock"></i>
                            </div>
                            <span class="text-muted small italic">Waiting for assignment</span>
                        @endif
                    </div>
                </div>

                @if ($task->ticket_id)
                    <div class="pt-3 mt-4 border-top border-secondary border-opacity-25">
                        <label class="text-muted extra-small d-block uppercase mb-2">Source Context</label>
                        <a href="{{ route('client.tickets.show', $task->ticket_id) }}"
                            class="btn btn-sm btn-outline-primary w-100 text-start">
                            <i class="fas fa-ticket-alt me-2"></i> Ticket #{{ $task->ticket_id }}
                        </a>
                    </div>
                @endif
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

        .font-outfit {
            font-family: 'Outfit', sans-serif;
        }

        .uppercase {
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .activity-timeline {
            position: relative;
            padding-left: 0.5rem;
        }

        .activity-timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 0;
            bottom: 0;
            width: 1px;
            background: rgba(255, 255, 255, 0.1);
        }
    </style>

    <!-- Init TinyMCE -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            const isDark = savedTheme === 'dark';

            tinymce.init({
                selector: '#reply-editor',
                height: 250,
                skin: isDark ? 'oxide-dark' : 'oxide',
                content_css: isDark ? 'dark' : 'default',
                menubar: false,
                statusbar: false,
                branding: false,
                plugins: 'autolink lists link image charmap preview anchor',
                toolbar: 'undo redo | bold italic | bullist numlist | removeformat',
                content_style: isDark ?
                    'body { background: transparent; color: rgba(255, 255, 255, 0.8); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px; margin: 10px; }' :
                    'body { background: transparent; color: #333; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px; margin: 10px; }'
            });
        });
    </script>
</x-client>
