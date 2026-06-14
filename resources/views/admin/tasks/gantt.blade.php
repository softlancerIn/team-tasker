<x-admin>
    <x-slot:title>
        Task Gantt | Team Tasker
    </x-slot:title>

    <div class="top-bar-premium mb-4">
        <div class="d-flex align-items-center gap-3">
            <h3 class="mb-0 fw-bold text-high">Gantt Chart</h3>
            <div class="ms-3 d-flex gap-2">
                <a href="{{ route('index') }}" class="btn-premium btn-premium-secondary py-1 px-3"
                    style="font-size: 0.8rem;">
                    <i class="fas fa-list me-1"></i> List
                </a>
                <a href="{{ route('tasks.board') }}" class="btn-premium btn-premium-secondary py-1 px-3"
                    style="font-size: 0.8rem;">
                    <i class="fas fa-columns me-1"></i> Kanban
                </a>
                <a href="{{ route('tasks.calendar') }}" class="btn-premium btn-premium-secondary py-1 px-3"
                    style="font-size: 0.8rem;">
                    <i class="fas fa-calendar-alt me-1"></i> Calendar
                </a>
                <a href="{{ route('tasks.gantt') }}" class="btn-premium btn-premium-secondary py-1 px-3 active"
                    style="font-size: 0.8rem; background: rgba(var(--primary-rgb), 0.15); color: var(--primary); border-color: rgba(var(--primary-rgb), 0.3);">
                    <i class="fas fa-project-diagram me-1"></i> Gantt
                </a>
            </div>
        </div>
        <div class="d-flex gap-2">
            <div class="d-flex gap-1">
                <button class="btn-premium btn-premium-secondary py-1 px-3" style="font-size: 0.8rem;"
                    onclick="gantt && gantt.change_view_mode('Day')">Day</button>
                <button class="btn-premium btn-premium-secondary py-1 px-3" style="font-size: 0.8rem;"
                    onclick="gantt && gantt.change_view_mode('Week')">Week</button>
                <button class="btn-premium btn-premium-secondary py-1 px-3" style="font-size: 0.8rem;"
                    onclick="gantt && gantt.change_view_mode('Month')">Month</button>
            </div>
            <a href="{{ route('create') }}" class="btn-premium btn-premium-primary">
                <i class="fas fa-plus me-1"></i> Create
            </a>
        </div>
    </div>

    <div class="glass-card p-0 overflow-hidden" style="border: 1px solid var(--border-main);">
        <div id="gantt-chart" style="min-height: 400px;"></div>
    </div>

    @push('scripts')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.6.1/frappe-gantt.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.6.1/frappe-gantt.js"></script>
        <script>
            let gantt;
            const getVar = (v) => getComputedStyle(document.documentElement).getPropertyValue(v).trim();

            document.addEventListener('DOMContentLoaded', function() {
                fetch('{{ route('tasks.gantt.data') }}')
                    .then(response => response.json())
                    .then(tasks => {
                        if (tasks.length === 0) {
                            document.getElementById('gantt-chart').innerHTML =
                                `<div class="text-center py-5" style="color: var(--text-low); font-size: 0.9rem; font-style: italic;">
                                    <i class="fas fa-project-diagram fa-3x mb-3 d-block" style="opacity: 0.2;"></i>
                                    No tasks with deadlines found.
                                </div>`;
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
                            view_mode: 'Week',
                            date_format: 'YYYY-MM-DD',
                            custom_popup_html: function(task) {
                                const danger = getVar('--danger');
                                const accent = getVar('--accent');
                                const primary = getVar('--primary');
                                const pColor = task.priority === 'Critical' ? danger : (task
                                    .priority === 'High' ? accent : primary);
                                const detailsUrl = '{{ route("details", ":id") }}'.replace(':id', task.id);
                                return `
                                <div style="width: 250px; background: var(--bg-surface); border: 1px solid var(--border-main); border-radius: 12px; padding: 16px; backdrop-filter: blur(10px);">
                                    <div style="font-weight: 700; font-size: 0.9rem; color: var(--text-high); margin-bottom: 6px; padding-bottom: 8px; border-bottom: 1px solid var(--border-subtle);">${task.name}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-low); margin-bottom: 10px;">${task.start} → ${task.end}</div>
                                    <div style="height: 6px; background: var(--bg-input); border-radius: 99px; margin-bottom: 10px; overflow: hidden;">
                                        <div style="height: 100%; width: ${task.progress}%; background: var(--primary); border-radius: 99px;"></div>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span style="font-size: 0.72rem; color: ${pColor}; font-weight: 600;">${task.priority ?? 'Normal'} · ${task.progress}%</span>
                                        <a href="${detailsUrl}" style="font-size: 0.72rem; background: var(--primary); color: white; padding: 3px 10px; border-radius: 6px; text-decoration: none;">View</a>
                                    </div>
                                </div>`;
                            }
                        });

                        applyGanttTheme();
                    });

                // Re-theme when dark/light toggled
                new MutationObserver(() => applyGanttTheme())
                    .observe(document.documentElement, {
                        attributes: true,
                        attributeFilter: ['data-theme']
                    });
            });

            function applyGanttTheme() {
                const textHigh = getVar('--text-high');
                const textLow = getVar('--text-low');
                const borderSub = getVar('--border-subtle');
                const primary = getVar('--primary');
                const bgInput = getVar('--bg-input');

                let style = document.getElementById('gantt-dynamic-theme');
                if (!style) {
                    style = document.createElement('style');
                    style.id = 'gantt-dynamic-theme';
                    document.head.appendChild(style);
                }
                style.textContent = `
                    .gantt .grid-header   { fill: ${bgInput}; stroke: ${borderSub}; }
                    .gantt .grid-row      { fill: transparent; }
                    .gantt .grid-row:nth-child(even) { fill: rgba(var(--primary-rgb), 0.01); }
                    .gantt .row-line      { stroke: ${borderSub}; }
                    .gantt .tick          { stroke: ${borderSub}; }
                    .gantt .upper-text    { fill: ${textLow}; font-size: 10px; font-weight: 600; text-transform: uppercase; }
                    .gantt .lower-text    { fill: ${textHigh}; font-size: 11px; }
                    .gantt .bar           { fill: ${primary}; }
                    .gantt .bar-progress  { fill: ${primary}; opacity: 0.7; }
                    .gantt .bar-label     { fill: #fff; font-size: 11px; }
                    .gantt .handle        { fill: #fff; }
                    .gantt .arrow         { stroke: ${borderSub}; }
                    .gantt-container      { background: transparent; }
                    .gantt .today-highlight { fill: rgba(var(--primary-rgb), 0.05); }
                `;
            }
        </script>
    @endpush
</x-admin>
