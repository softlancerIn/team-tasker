<x-admin>
    <x-slot:title>
        Admin Dashboard | Team Tasker
    </x-slot:title>

    <div class="row g-4 mb-4">
        <!-- Project Overall Stats -->
        <div class="col-md-3">
            <div class="glass-card h-100">
                <div class="stat-icon icon-primary">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div class="text-muted extra-small uppercase mb-1">Total Project Tasks</div>
                <div class="h3 mb-0 fw-bold text-white">{{ $totalTasks }}</div>
                <div class="mt-2 progress bg-white bg-opacity-10" style="height: 4px;">
                    <div class="progress-bar bg-primary" style="width: 100%"></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card h-100">
                <div class="stat-icon icon-accent">
                    <i class="fas fa-check-double"></i>
                </div>
                <div class="text-muted extra-small uppercase mb-1">Completed Tasks</div>
                <div class="h3 mb-0 fw-bold text-white">{{ $completedTasksCount }}</div>
                <div class="mt-2 progress bg-white bg-opacity-10" style="height: 4px;">
                    <div class="progress-bar bg-success"
                        style="width: {{ $totalTasks > 0 ? ($completedTasksCount / $totalTasks) * 100 : 0 }}%"></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card h-100">
                <div class="stat-icon icon-warning">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="text-muted extra-small uppercase mb-1">Total Tickets</div>
                <div class="h3 mb-0 fw-bold text-white">{{ $totalTickets }}</div>
                <div class="mt-2 text-primary extra-small">Across all clients</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card h-100">
                <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: var(--primary);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="text-muted extra-small uppercase mb-1">Team Members</div>
                <div class="h3 mb-0 fw-bold text-white">{{ $totalUsers }}</div>
                <div class="mt-2 text-main-50 extra-small">Active in system</div>
            </div>
        </div>
    </div>

    <!-- Health & Progress Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="glass-card border-danger border-opacity-10">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-danger extra-small uppercase mb-1">Critical Tasks</h6>
                        <h4 class="mb-0 text-white">{{ $criticalTasksCount }}</h4>
                    </div>
                    <div
                        class="stat-icon bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 mb-0">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <p class="text-muted small mt-2 mb-0">Tasks requiring immediate attention across the project.</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-muted extra-small uppercase mb-0">Project Completion</h6>
                    <span class="text-primary fw-bold">{{ round($projectProgress, 1) }}%</span>
                </div>
                <div class="progress bg-white bg-opacity-10" style="height: 10px; border-radius: 5px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                        style="width: {{ $projectProgress }}%"></div>
                </div>
                <p class="text-muted small mt-2 mb-0">Weighted average progress of all tracked project tasks.</p>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="glass-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">Task Creation Activity</h5>
                    <span
                        class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">Last
                        7 Days</span>
                </div>
                <div style="position: relative; height: 350px; width: 100%;">
                    <canvas id="taskChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="glass-card h-100">
                <h5 class="mb-4">Project Activity Log</h5>
                <div class="activity-timeline">
                    @forelse($recentActivities as $log)
                        <div class="d-flex gap-3 mb-4">
                            <div class="position-relative">
                                <div class="avatar" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                    {{ substr($log->user->name, 0, 1) }}
                                </div>
                                @if (!$loop->last)
                                    <div class="position-absolute start-50 top-100 border-start border-secondary border-opacity-25"
                                        style="height: 25px; transform: translateX(-50%);"></div>
                                @endif
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex justify-content-between">
                                    <span class="text-white small fw-bold">{{ $log->user->name }}</span>
                                    <span class="text-muted extra-small">{{ $log->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="text-main-50 extra-small text-truncate mt-1">
                                    {{ $log->type == 'message' ? 'Messaged on' : 'Updated' }}
                                    <a href="{{ route('details', $log->task_id) }}"
                                        class="text-primary decoration-none">#{{ $log->task_id }}</a>
                                </div>
                                <div class="text-muted extra-small italic mt-1 text-truncate">
                                    "{!! Str::limit(strip_tags($log->note), 40) !!}"
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">No global activity yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Personal Tasks Section -->
    <div class="glass-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">My Recent Tasks</h5>
            <a href="{{ route('index') }}" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="table-responsive">
            <table class="table text-white align-middle mb-0">
                <thead>
                    <tr class="text-muted extra-small uppercase border-bottom border-white border-opacity-10">
                        <th class="border-0 bg-transparent">Task</th>
                        <th class="border-0 bg-transparent">Status</th>
                        <th class="border-0 bg-transparent">Priority</th>
                        <th class="border-0 bg-transparent">Progress</th>
                        <th class="border-0 bg-transparent text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($personalTasks as $task)
                        <tr class="border-bottom border-secondary border-opacity-10">
                            <td class="bg-transparent py-3">
                                <div class="fw-bold small">{{ $task->title }}</div>
                                <div class="text-muted extra-small mt-1">Due:
                                    {{ $task->deadline ? $task->deadline->format('M d') : 'No Date' }}</div>
                            </td>
                            <td class="bg-transparent py-3">
                                <span
                                    class="badge bg-{{ $task->status->color ?? 'secondary' }} bg-opacity-10 text-{{ $task->status->color ?? 'secondary' }} extra-small">
                                    {{ $task->status->name ?? 'Pending' }}
                                </span>
                            </td>
                            <td class="bg-transparent py-3">
                                <span
                                    class="extra-small text-{{ $task->priority == 'Critical' ? 'danger' : ($task->priority == 'High' ? 'warning' : 'info') }}">
                                    <i class="fas fa-flag me-1"></i> {{ $task->priority }}
                                </span>
                            </td>
                            <td class="bg-transparent py-3" style="width: 150px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress bg-white bg-opacity-10 flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar bg-primary" style="width: {{ $task->progress }}%">
                                        </div>
                                    </div>
                                    <span class="extra-small">{{ $task->progress }}%</span>
                                </div>
                            </td>
                            <td class="bg-transparent py-3 text-end">
                                <a href="{{ route('details', $task->id) }}"
                                    class="btn btn-sm btn-outline-secondary border-0">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted small">No personal tasks assigned.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
