<x-admin>
    <x-slot:title>
        Admin Dashboard | Team Tasker
    </x-slot:title>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="glass-card">
                <div class="stat-icon icon-primary">
                    <i class="fas fa-tasks"></i>
                </div>
                <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;">Total Tasks</div>
                <div style="font-size: 1.8rem; font-weight: 700;">{{ count($tasks) }}</div>
                <div style="font-size: 0.8rem; color: var(--accent); margin-top: 0.5rem;">
                    <i class="fas fa-arrow-up"></i> 12% from last week
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card">
                <div class="stat-icon icon-accent">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;">Completed</div>
                <div style="font-size: 1.8rem; font-weight: 700;">
                    {{ $tasks->filter(fn($t) => $t->status?->slug === 'completed')->count() }}
                </div>
                <div style="font-size: 0.8rem; color: var(--accent); margin-top: 0.5rem;">
                    <i class="fas fa-arrow-up"></i> 5% increase
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card">
                <div class="stat-icon icon-warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;">Pending</div>
                <div style="font-size: 1.8rem; font-weight: 700;">
                    {{ $tasks->filter(fn($t) => $t->status?->slug !== 'completed')->count() }}</div>
                <div style="font-size: 0.8rem; color: #f59e0b; margin-top: 0.5rem;">
                    Attention required
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="glass-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">Task Performance</h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                            data-bs-toggle="dropdown">
                            This Month
                        </button>
                    </div>
                </div>
                <div style="position: relative; height: 300px; width: 100%;">
                    <canvas id="taskChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="glass-card h-100">
                <h5 class="mb-4">Recent Tasks</h5>
                <div class="list-group list-group-flush bg-transparent">
                    @forelse($tasks->take(5) as $task)
                        <div class="list-group-item bg-transparent border-0 px-0 py-3 d-flex align-items-center gap-3">
                            <div class="avatar"
                                style="width: 32px; height: 32px; font-size: 0.7rem; background: rgba(255,255,255,0.1)">
                                {{ substr($task->title, 0, 1) }}
                            </div>
                            <div class="flex-grow-1">
                                <div style="font-size: 0.9rem; font-weight: 500;">{{ $task->title }}</div>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">
                                        {{ $task->created_at->diffForHumans() }}</div>
                                    @if ($task->assignedTo)
                                        <span
                                            class="badge bg-white bg-opacity-5 text-muted border border-white border-opacity-10 rounded-pill py-1 px-2"
                                            style="font-size: 0.65rem;">
                                            <i class="fas fa-user-check me-1"></i> {{ $task->assignedTo->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('details', $task->id) }}" class="text-primary"><i
                                    class="fas fa-chevron-right"></i></a>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">No tasks found.</div>
                    @endforelse
                </div>
                <div class="mt-4">
                    <a href="{{ route('index') }}" class="btn btn-primary w-100">View All Tasks</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('taskChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Tasks Created',
                        data: @json($chartData),
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointBackgroundColor: '#6366f1',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)'
                            },
                            ticks: {
                                color: '#94a3b8'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#94a3b8'
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-admin>
