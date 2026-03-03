<x-admin>
    <x-slot:title>
        Admin Dashboard | Team Tasker
    </x-slot:title>

    <div class="row g-4 mb-4">
        <!-- Project Overall Stats -->
        <div class="col-md-3">
            <div class="glass-card h-100" style="border: 1px solid var(--border-main);">
                <div class="stat-icon-premium icon-primary-premium mb-3">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div class="heading-label" style="font-size: 0.75rem;">Total Project Tasks</div>
                <h3 class="h2 fw-bold mb-1" style="color: var(--text-high);">{{ $totalTasks }}</h3>
                <div class="mt-3 progress-premium" style="height: 6px;">
                    <div class="progress-bar-premium" style="width: 100%"></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card h-100" style="border: 1px solid var(--border-main);">
                <div class="stat-icon-premium icon-success-premium mb-3">
                    <i class="fas fa-check-double"></i>
                </div>
                <div class="heading-label" style="font-size: 0.75rem;">Completed Tasks</div>
                <h3 class="h2 fw-bold mb-1" style="color: var(--text-high);">{{ $completedTasksCount }}</h3>
                <div class="mt-3 progress-premium" style="height: 6px;">
                    <div class="progress-bar-premium"
                        style="width: {{ $totalTasks > 0 ? ($completedTasksCount / $totalTasks) * 100 : 0 }}%; background: var(--accent);">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card h-100" style="border: 1px solid var(--border-main);">
                <div class="stat-icon-premium mb-3"
                    style="background: rgba(var(--accent-h), var(--accent-s), var(--accent-l), 0.1); color: var(--accent);">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="heading-label" style="font-size: 0.75rem;">Total Tickets</div>
                <h3 class="h2 fw-bold mb-1" style="color: var(--text-high);">{{ $totalTickets }}</h3>
                <div class="mt-2 text-low" style="font-size: 0.7rem;">Across all clients</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card h-100" style="border: 1px solid var(--border-main);">
                <div class="stat-icon-premium icon-primary-premium mb-3">
                    <i class="fas fa-users"></i>
                </div>
                <div class="heading-label" style="font-size: 0.75rem;">Team Members</div>
                <h3 class="h2 fw-bold mb-1" style="color: var(--text-high);">{{ $totalUsers }}</h3>
                <div class="mt-2 text-low" style="font-size: 0.7rem;">Active in system</div>
            </div>
        </div>
    </div>

    <!-- Health & Progress Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="glass-card"
                style="border: 1px solid rgba(var(--danger-rgb), 0.2); background: rgba(var(--danger-rgb), 0.02);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="heading-label" style="color: var(--danger); font-size: 0.75rem;">Critical Tasks
                        </div>
                        <h4 class="mb-0 fw-bold" style="color: var(--text-high);">{{ $criticalTasksCount }}</h4>
                    </div>
                    <div class="stat-icon-premium mb-0"
                        style="background: rgba(var(--danger-rgb), 0.1); color: var(--danger); border: 1px solid rgba(var(--danger-rgb), 0.2);">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <p class="text-low small mt-2 mb-0" style="font-size: 0.8rem;">Tasks requiring immediate attention
                    across the project.</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="glass-card" style="border: 1px solid var(--border-main);">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="heading-label" style="font-size: 0.75rem;">Project Completion Trend</div>
                    <span class="fw-bold" style="color: var(--primary);">{{ round($projectProgress, 1) }}%</span>
                </div>
                <div class="progress-premium" style="height: 8px;">
                    <div class="progress-bar-premium"
                        style="width: {{ $projectProgress }}%; background: var(--primary); box-shadow: 0 0 15px rgba(var(--primary-rgb), 0.3);">
                    </div>
                </div>
                <p class="text-low small mt-3 mb-0" style="font-size: 0.8rem;">Weighted average progress of all tracked
                    project tasks.</p>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="glass-card h-100" style="border: 1px solid var(--border-main);">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold mb-0" style="color: var(--text-high);">Task Creation Activity</h5>
                        <p class="mb-0 text-low" style="font-size: 0.72rem; margin-top: 2px;">Tasks created over time
                        </p>
                    </div>
                    <div class="d-flex gap-1" id="chartPeriodBtns" role="group">
                        <button class="chart-period-btn active" data-period="7d"
                            style="font-size: 0.72rem; padding: 4px 10px; border-radius: 20px; border: 1px solid var(--border-main); cursor: pointer; background: var(--primary); color: #fff; font-weight: 600; transition: all 0.2s;">
                            7D
                        </button>
                        <button class="chart-period-btn" data-period="30d"
                            style="font-size: 0.72rem; padding: 4px 10px; border-radius: 20px; border: 1px solid var(--border-main); cursor: pointer; background: var(--bg-input); color: var(--text-medium); font-weight: 500; transition: all 0.2s;">
                            30D
                        </button>
                        <button class="chart-period-btn" data-period="90d"
                            style="font-size: 0.72rem; padding: 4px 10px; border-radius: 20px; border: 1px solid var(--border-main); cursor: pointer; background: var(--bg-input); color: var(--text-medium); font-weight: 500; transition: all 0.2s;">
                            90D
                        </button>
                        <button class="chart-period-btn" data-period="all"
                            style="font-size: 0.72rem; padding: 4px 10px; border-radius: 20px; border: 1px solid var(--border-main); cursor: pointer; background: var(--bg-input); color: var(--text-medium); font-weight: 500; transition: all 0.2s;">
                            All
                        </button>
                    </div>
                </div>
                <!-- Summary chips -->
                <div class="d-flex gap-3 mb-4" id="chartSummaryRow">
                    <div class="d-flex align-items-center gap-2">
                        <span
                            style="width: 8px; height: 8px; border-radius: 50%; background: var(--primary); display: inline-block;"></span>
                        <span class="text-low" style="font-size: 0.72rem;">Total in period: <strong class="text-high"
                                id="chartTotal">{{ array_sum($chartData) }}</strong></span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span
                            style="width: 8px; height: 8px; border-radius: 50%; background: var(--accent); display: inline-block;"></span>
                        <span class="text-low" style="font-size: 0.72rem;">Peak day: <strong class="text-high"
                                id="chartPeak">{{ count($chartData) ? max($chartData) : 0 }}</strong></span>
                    </div>
                </div>
                <div style="position: relative; height: 300px; width: 100%;">
                    <canvas id="taskChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="glass-card h-100" style="border: 1px solid var(--border-main);">
                <h5 class="fw-bold mb-4" style="color: var(--text-high);">Project Activity Log</h5>
                <div class="activity-timeline">
                    @forelse($recentActivities as $log)
                        <div class="d-flex gap-3 mb-4">
                            <div class="position-relative">
                                <div class="avatar-premium"
                                    style="width: 36px; height: 36px; font-size: 0.8rem; border: 2px solid var(--border-main);">
                                    {{ substr($log->user->name, 0, 1) }}
                                </div>
                                @if (!$loop->last)
                                    <div class="position-absolute start-50 top-100 border-start"
                                        style="height: 20px; transform: translateX(-50%); border-color: var(--border-subtle) !important;">
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold"
                                        style="color: var(--text-high); font-size: 0.85rem;">{{ $log->user->name }}</span>
                                    <span class="text-low"
                                        style="font-size: 0.65rem;">{{ $log->created_at->diffForHumans() }}</span>
                                </div>
                                <div style="color: var(--text-medium); font-size: 0.75rem; margin-top: 2px;">
                                    {{ $log->type == 'message' ? 'Messaged on' : 'Updated' }}
                                    <a href="{{ route('details', $log->task_id) }}"
                                        class="text-decoration-none fw-medium"
                                        style="color: var(--primary);">#{{ $log->task_id }}</a>
                                </div>
                                <div class="text-low italic mt-1 text-truncate"
                                    style="font-size: 0.75rem; opacity: 0.8;">
                                    "{!! Str::limit(strip_tags($log->note), 40) !!}"
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-low italic" style="font-size: 0.85rem;">No global activity
                            yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Personal Tasks Section -->
    <div class="glass-card p-0 overflow-hidden" style="border: 1px solid var(--border-main);">
        <div class="d-flex justify-content-between align-items-center p-4 border-bottom border-main">
            <h5 class="fw-bold mb-0" style="color: var(--text-high);">My Recent Tasks</h5>
            <a href="{{ route('index') }}" class="btn-premium btn-premium-secondary btn-sm px-3 py-1"
                style="font-size: 0.75rem;">
                View All Tasks
            </a>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4 py-3 border-0">Task Details</th>
                        <th class="py-3 border-0">Status</th>
                        <th class="py-3 border-0">Priority</th>
                        <th class="py-3 border-0">Progress</th>
                        <th class="pe-4 py-3 border-0 text-end">Action</th>
                    </tr>
                <tbody style="border: none;">
                    @forelse($personalTasks as $task)
                        <tr class="align-middle"
                            style="border-bottom: 1px solid var(--border-subtle); background: transparent !important;">
                            <td class="ps-4 py-3" style="border: none; background: transparent !important;">
                                <div class="fw-bold" style="color: var(--text-high); font-size: 0.9rem;">
                                    {{ $task->title }}</div>
                                <div class="text-low mt-1" style="font-size: 0.7rem;">
                                    <i class="far fa-calendar-alt me-1"></i> Due:
                                    {{ $task->deadline ? $task->deadline->format('M d, Y') : 'Not Set' }}
                                </div>
                            </td>
                            <td style="border: none; background: transparent !important;">
                                <span class="badge-premium"
                                    style="background: {{ ($task->status->color ?? 'secondary') == 'success' ? 'rgba(var(--accent-rgb), 0.1)' : 'var(--bg-input)' }};
                                           color: {{ ($task->status->color ?? 'secondary') == 'success' ? 'var(--accent)' : 'var(--text-high)' }}; border: 1px solid var(--border-main); white-space: nowrap;">
                                    {{ $task->status->name ?? 'Pending' }}
                                </span>
                            </td>
                            <td style="border: none; background: transparent !important;">
                                <span class="d-flex align-items-center gap-1"
                                    style="color: {{ $task->priority == 'Critical' ? 'var(--danger)' : ($task->priority == 'High' ? 'var(--accent)' : 'var(--text-low)') }}; font-size: 0.8rem; font-weight: 600;">
                                    <i class="fas fa-flag" style="font-size: 0.7rem;"></i> {{ $task->priority }}
                                </span>
                            </td>
                            <td style="width: 180px; border: none; background: transparent !important;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress-premium flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar-premium"
                                            style="width: {{ $task->progress }}%; background: var(--primary);">
                                        </div>
                                    </div>
                                    <span class="text-high fw-bold"
                                        style="font-size: 0.7rem; min-width: 35px;">{{ $task->progress }}%</span>
                                </div>
                            </td>
                            <td class="pe-4 text-end" style="border: none; background: transparent !important;">
                                <a href="{{ route('details', $task->id) }}"
                                    class="btn-premium btn-premium-secondary btn-sm p-0 d-inline-flex align-items-center justify-content-center shadow-none"
                                    style="width: 32px; height: 32px; border-radius: 50%; background: var(--bg-input); border: 1px solid var(--border-main);">
                                    <i class="fas fa-chevron-right"
                                        style="font-size: 0.8rem; color: var(--text-medium);"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-low italic"
                                style="border: none; background: transparent !important;">No personal tasks assigned.
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
            const getVar = (v) => getComputedStyle(document.documentElement).getPropertyValue(v).trim();

            // All period datasets from server
            const periods = {
                '7d': {
                    labels: @json($chart7d['labels']),
                    data: @json($chart7d['data'])
                },
                '30d': {
                    labels: @json($chart30d['labels']),
                    data: @json($chart30d['data'])
                },
                '90d': {
                    labels: @json($chart90d['labels']),
                    data: @json($chart90d['data'])
                },
                'all': {
                    labels: @json($chartAll['labels']),
                    data: @json($chartAll['data'])
                },
            };

            function makeGradient() {
                const g = ctx.createLinearGradient(0, 0, 0, 300);
                g.addColorStop(0, `rgba(99, 102, 241, 0.35)`);
                g.addColorStop(1, `rgba(99, 102, 241, 0.0)`);
                return g;
            }

            const activityChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: periods['7d'].labels,
                    datasets: [{
                        label: 'Tasks Created',
                        data: periods['7d'].data,
                        borderColor: getVar('--primary'),
                        backgroundColor: makeGradient(),
                        fill: true,
                        tension: 0.45,
                        borderWidth: 2.5,
                        pointBackgroundColor: getVar('--primary'),
                        pointBorderColor: getVar('--bg-surface'),
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 7,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 500,
                        easing: 'easeInOutQuart'
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'var(--bg-surface)',
                            borderColor: 'var(--border-main)',
                            borderWidth: 1,
                            titleColor: getVar('--text-high'),
                            bodyColor: getVar('--text-medium'),
                            padding: 10,
                            callbacks: {
                                title: (items) => items[0].label,
                                label: (item) => ` ${item.raw} task${item.raw !== 1 ? 's' : ''} created`,
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                color: getVar('--text-low'),
                                font: {
                                    size: 11
                                }
                            },
                            grid: {
                                color: getVar('--border-subtle')
                            },
                            border: {
                                display: false
                            },
                        },
                        x: {
                            ticks: {
                                color: getVar('--text-low'),
                                font: {
                                    size: 11
                                },
                                maxTicksLimit: 12,
                                maxRotation: 30,
                            },
                            grid: {
                                display: false
                            },
                            border: {
                                display: false
                            },
                        }
                    }
                }
            });

            // Period switcher
            document.querySelectorAll('.chart-period-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const period = this.dataset.period;
                    const {
                        labels,
                        data
                    } = periods[period];

                    // Update chart data
                    activityChart.data.labels = labels;
                    activityChart.data.datasets[0].data = data;
                    activityChart.data.datasets[0].backgroundColor = makeGradient();
                    activityChart.update();

                    // Update summary chips
                    const total = data.reduce((a, b) => a + b, 0);
                    const peak = data.length ? Math.max(...data) : 0;
                    document.getElementById('chartTotal').textContent = total;
                    document.getElementById('chartPeak').textContent = peak;

                    // Update active button styling
                    document.querySelectorAll('.chart-period-btn').forEach(b => {
                        b.style.background = getVar('--bg-input');
                        b.style.color = getVar('--text-medium');
                        b.style.fontWeight = '500';
                        b.classList.remove('active');
                    });
                    this.style.background = getVar('--primary');
                    this.style.color = '#fff';
                    this.style.fontWeight = '600';
                    this.classList.add('active');
                });
            });

            // Theme observer
            new MutationObserver(() => {
                activityChart.options.scales.y.grid.color = getVar('--border-subtle');
                activityChart.options.scales.y.ticks.color = getVar('--text-low');
                activityChart.options.scales.x.ticks.color = getVar('--text-low');
                activityChart.data.datasets[0].borderColor = getVar('--primary');
                activityChart.data.datasets[0].pointBackgroundColor = getVar('--primary');
                activityChart.data.datasets[0].pointBorderColor = getVar('--bg-surface');
                activityChart.data.datasets[0].backgroundColor = makeGradient();
                activityChart.update();
                // Recolor active btn
                const activeBtn = document.querySelector('.chart-period-btn.active');
                if (activeBtn) {
                    activeBtn.style.background = getVar('--primary');
                    activeBtn.style.color = '#fff';
                }
                document.querySelectorAll('.chart-period-btn:not(.active)').forEach(b => {
                    b.style.background = getVar('--bg-input');
                    b.style.color = getVar('--text-medium');
                });
            }).observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['data-theme']
            });
        });
    </script>
</x-admin>
