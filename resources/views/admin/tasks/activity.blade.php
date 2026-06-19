<x-admin title="Task Activity">
    <div class="top-bar-premium">
        <div>
            <h1 class="h3 fw-semibold mb-1 text-high">Task Activity</h1>
            <p class="text-low mb-0" style="font-size: 0.9rem;">Global feed of all task interactions, updates, and assignments.</p>
        </div>
    </div>

    <div class="data-grid-wrapper mb-5 p-4" style="background: var(--bg-surface); border: 1px solid var(--border-main); border-radius: var(--radius-lg);">
        <div class="activity-timeline">
            @forelse($activities as $log)
                <div class="d-flex gap-3 mb-4">
                    <div class="position-relative">
                        <div class="avatar-premium"
                            style="width: 32px; height: 32px; font-size: 0.8rem; background: {{ $log->type == 'message' ? 'rgba(var(--primary-rgb), 0.1)' : 'var(--bg-input)' }}; color: {{ $log->type == 'message' ? 'var(--primary)' : 'var(--text-medium)' }};">
                            {{ substr($log->user->name, 0, 1) }}
                        </div>
                        @if (!$loop->last)
                            <div class="position-absolute start-50 top-100 border-start border-secondary border-opacity-25"
                                style="height: 20px; transform: translateX(-50%);"></div>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <span class="fw-bold text-high small">{{ $log->user->name }}</span>
                                <span class="text-low small mx-1">on</span>
                                <a href="{{ route('details', $log->task_id) }}" class="fw-bold text-primary small text-decoration-none">
                                    {{ $log->task->title ?? 'Unknown Task' }}
                                </a>
                            </div>
                            <span class="text-low extra-small">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                        @if ($log->type == 'message')
                            <span class="badge-premium mb-2"
                                style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary); border: 1px solid rgba(var(--primary-rgb), 0.2); font-size: 0.65rem;">
                                <i class="fas fa-external-link-alt me-1"></i> Client Message
                            </span>
                        @else
                            <span class="badge-premium mb-2"
                                style="background: var(--bg-input); color: var(--text-low); border: 1px solid var(--border-subtle); font-size: 0.65rem;">
                                <i class="fas fa-history me-1"></i> System Activity
                            </span>
                        @endif
                        <div class="text-main-50 small ck-content">{!! $log->note !!}</div>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted small">
                    No activity logged yet.
                </div>
            @endforelse

            <div class="mt-4">
                {{ $activities->links() }}
            </div>
        </div>
    </div>
</x-admin>
