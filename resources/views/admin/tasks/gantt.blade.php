<x-admin>
    <x-slot:title>
        Task Gantt | Team Tasker
    </x-slot:title>

    <div class="top-bar mb-4">
        <div class="d-flex align-items-center gap-3">
            <h3 class="mb-0">Task Gantt</h3>
            <div class="ms-3 d-flex gap-2">
                <a href="{{ route('index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-list me-1"></i> List
                </a>
                <a href="{{ route('tasks.board') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-columns me-1"></i> Kanban
                </a>
                <a href="{{ route('tasks.calendar') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-calendar-alt me-1"></i> Calendar
                </a>
                <a href="{{ route('tasks.gantt') }}" class="btn btn-outline-secondary btn-sm active">
                    <i class="fas fa-project-diagram me-1"></i> Gantt
                </a>
            </div>
        </div>
        <div class="d-flex gap-2">
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-secondary" onclick="gantt.change_view_mode('Day')">Day</button>
                <button class="btn btn-outline-secondary" onclick="gantt.change_view_mode('Week')">Week</button>
                <button class="btn btn-outline-secondary" onclick="gantt.change_view_mode('Month')">Month</button>
            </div>
            <a href="{{ route('create') }}" class="btn btn-primary btn-sm px-3">
                <i class="fas fa-plus me-1"></i> Create
            </a>
        </div>
    </div>

    <div class="glass-card p-0 overflow-hidden" style="border: 1px solid var(--border-color);">
        <div id="gantt-chart"></div>
    </div>

    @push('scripts')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt/dist/frappe-gantt.css">
        <script src="https://cdn.jsdelivr.net/npm/frappe-gantt/dist/frappe-gantt.js"></script>
        <script>
            let gantt;
            document.addEventListener('DOMContentLoaded', function() {
                fetch('{{ route('tasks.gantt.data') }}')
                    .then(response => response.json())
                    .then(tasks => {
                        if (tasks.length === 0) {
                            document.getElementById('gantt-chart').innerHTML =
                                '<div class="text-center py-5 text-muted">No tasks with deadlines found.</div>';
                            return;
                        }

                        gantt = new Gantt("#gantt-chart", tasks, {
                            header_height: 50,
                            column_width: 30,
                            step: 24,
                            view_modes: ['Day', 'Week', 'Month'],
                            bar_height: 20,
                            bar_corner_radius: 3,
                            arrow_curve: 5,
                            padding: 18,
                            view_mode: 'Day',
                            date_format: 'YYYY-MM-DD',
                            custom_popup_html: function(task) {
                                return `
                                <div class="p-3 bg-dark text-white rounded shadow-lg border border-secondary" style="width: 250px;">
                                    <div class="small fw-bold mb-1 border-bottom border-secondary pb-1">${task.name}</div>
                                    <div class="extra-small text-muted mb-2">${task.start} - ${task.end}</div>
                                    <div class="progress mb-2" style="height: 6px; background: rgba(255,255,255,0.1);">
                                        <div class="progress-bar bg-primary" style="width: ${task.progress}%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="extra-small text-muted">Progress: ${task.progress}%</span>
                                        <a href="/admin/tasks/details/${task.id}" class="btn btn-xs btn-primary py-0 px-2 extra-small">View</a>
                                    </div>
                                </div>
                            `;
                            }
                        });
                    });
            });
        </script>
        <style>
            .gantt .grid-header {
                fill: rgba(255, 255, 255, 0.02);
                stroke: rgba(255, 255, 255, 0.05);
            }

            .gantt .grid-row {
                fill: transparent;
            }

            .gantt .grid-row:nth-child(even) {
                fill: rgba(255, 255, 255, 0.01);
            }

            .gantt .row-line {
                stroke: rgba(255, 255, 255, 0.05);
            }

            .gantt .tick {
                stroke: rgba(255, 255, 255, 0.05);
            }

            .gantt .upper-text {
                fill: #64748b;
                font-size: 10px;
                font-weight: 600;
                text-transform: uppercase;
            }

            .gantt .lower-text {
                fill: #fff;
                font-size: 11px;
            }

            .gantt .bar {
                fill: var(--primary);
            }

            .gantt .bar-progress {
                fill: var(--primary-dark);
            }

            .gantt .bar-label {
                fill: #fff;
                font-size: 12px;
            }

            .gantt .handle {
                fill: #fff;
            }

            .gantt .arrow {
                stroke: rgba(255, 255, 255, 0.2);
            }
        </style>
    @endpush
</x-admin>
