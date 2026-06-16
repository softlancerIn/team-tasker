<div class="activity-timeline">
    @forelse($logs as $log)
        <div class="d-flex gap-3 mb-4">
            <div class="position-relative">
                <div class="avatar-premium"
                    style="width: 32px; height: 32px; font-size: 0.8rem; background: {{ $log->type == 'message' ? 'rgba(var(--primary-rgb), 0.1)' : 'var(--bg-input)' }}; color: {{ $log->type == 'message' ? 'var(--primary)' : 'var(--text-medium)' }};">
                    {{ substr($log->user->name, 0, 1) }}
                </div>
                @if (!$loop->last || $hasMore)
                    <div class="position-absolute start-50 top-100 border-start border-secondary border-opacity-25"
                        style="height: 20px; transform: translateX(-50%);"></div>
                @endif
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-bold text-high small">{{ $log->user->name }}</span>
                    <span
                        class="text-low extra-small">{{ $log->created_at->diffForHumans() }}</span>
                </div>
                @if ($log->type == 'message')
                    <span class="badge-premium mb-2"
                        style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary); border: 1px solid rgba(var(--primary-rgb), 0.2); font-size: 0.65rem;">
                        <i class="fas fa-external-link-alt me-1"></i> Client Message
                    </span>
                @else
                    <span class="badge-premium mb-2"
                        style="background: var(--bg-input); color: var(--text-low); border: 1px solid var(--border-subtle); font-size: 0.65rem;">
                        <i class="fas fa-lock me-1"></i> Internal Note
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

    @if($hasMore)
        <div class="text-center mt-3">
            <button wire:click="loadMore" class="btn-premium btn-premium-secondary btn-sm px-4">
                <span wire:loading.remove wire:target="loadMore">Load More</span>
                <span wire:loading wire:target="loadMore">Loading...</span>
            </button>
        </div>
    @endif
</div>
