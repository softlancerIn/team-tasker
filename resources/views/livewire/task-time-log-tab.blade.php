<div class="glass-card table-responsive">
    <table class="table text-main align-middle mb-0">
        <thead>
            <tr class="heading-label">
                <th class="border-0 bg-transparent">Date</th>
                <th class="border-0 bg-transparent">User</th>
                <th class="border-0 bg-transparent">Description</th>
                <th class="border-0 bg-transparent">Duration</th>
                <th class="border-0 bg-transparent">Mode</th>
            </tr>
        </thead>
        <tbody>
            @forelse($timeLogs as $timeLog)
                <tr class="border-bottom border-secondary border-opacity-10">
                    <td class="bg-transparent py-3 text-main">
                        {{ $timeLog->start_time->format('d/m/Y') }}
                    </td>
                    <td class="bg-transparent py-3">
                        <span class="text-main">{{ $timeLog->user->name }}</span>
                    </td>
                    <td class="bg-transparent py-3">
                        <span class="text-main-50 small">
                            Worked on {{ $timeLog->start_time->format('d-F-Y') }}
                            ({{ $timeLog->start_time->format('h:i A') }} To
                            {{ $timeLog->end_time ? $timeLog->end_time->format('h:i A') : 'Now' }})
                        </span>
                        @if ($timeLog->description)
                            <div class="text-main small mt-1">
                                {{ $timeLog->description }}</div>
                        @endif
                    </td>
                    <td class="bg-transparent py-3">
                        <span class="fw-bold text-primary small">
                            @if ($timeLog->end_time)
                                {{ floor(abs($timeLog->duration) / 3600) }}h
                                {{ floor((abs($timeLog->duration) % 3600) / 60) }}m
                            @else
                                Active
                            @endif
                        </span>
                    </td>
                    <td class="bg-transparent py-3">
                        <span class="text-main small">
                            {{ $timeLog->mode ?? 'inside office' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5"
                        class="text-center py-5 text-muted small bg-transparent">
                        No time logged yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($hasMore)
        <div class="text-center mt-4 mb-2">
            <button wire:click="loadMore" class="btn-premium btn-premium-secondary btn-sm px-4">
                <span wire:loading.remove wire:target="loadMore">Load More</span>
                <span wire:loading wire:target="loadMore">Loading...</span>
            </button>
        </div>
    @endif
</div>
