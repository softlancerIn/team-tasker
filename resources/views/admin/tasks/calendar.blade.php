<x-admin>
    <x-slot:title>
        Task Calendar | Team Tasker
    </x-slot:title>

    <div class="top-bar-premium mb-4">
        <div class="d-flex align-items-center gap-3">
            <h3 class="mb-0 fw-bold text-high">Task Calendar</h3>
            <div class="ms-3 d-flex gap-2">
                <a href="{{ route('index') }}" class="btn-premium btn-premium-secondary py-1 px-3"
                    style="font-size: 0.8rem;">
                    <i class="fas fa-list me-1"></i> List
                </a>
                <a href="{{ route('tasks.board') }}" class="btn-premium btn-premium-secondary py-1 px-3"
                    style="font-size: 0.8rem;">
                    <i class="fas fa-columns me-1"></i> Kanban
                </a>
                <a href="{{ route('tasks.calendar') }}" class="btn-premium btn-premium-secondary py-1 px-3 active"
                    style="font-size: 0.8rem; background: rgba(var(--primary-rgb), 0.15); color: var(--primary); border-color: rgba(var(--primary-rgb), 0.3);">
                    <i class="fas fa-calendar-alt me-1"></i> Calendar
                </a>
                <a href="{{ route('tasks.gantt') }}" class="btn-premium btn-premium-secondary py-1 px-3"
                    style="font-size: 0.8rem;">
                    <i class="fas fa-project-diagram me-1"></i> Gantt
                </a>
            </div>
        </div>
        <a href="{{ route('create') }}" class="btn-premium btn-premium-primary">
            <i class="fas fa-plus me-1"></i> Create Task
        </a>
    </div>

    <div class="glass-card" style="border: 1px solid var(--border-main);">
        <div id="calendar" style="min-height: 700px;"></div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
        <script>
            const getThemeVar = (v) => getComputedStyle(document.documentElement).getPropertyValue(v).trim();

            function buildCalendar() {
                var calendarEl = document.getElementById('calendar');
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    events: '{{ route('tasks.calendar.events') }}',
                    eventClick: function(info) {
                        window.location.href = '/admin/tasks/details/' + info.event.id;
                    },
                    eventContent: function(arg) {
                        let priority = arg.event.extendedProps.priority;
                        let color = priority === 'Critical' ?
                            getThemeVar('--danger') :
                            (priority === 'High' ? getThemeVar('--accent') : getThemeVar('--primary'));

                        return {
                            html: `<div class="p-1 px-2 rounded small d-flex align-items-center gap-2" style="background: ${color}22; border: 1px solid ${color}44; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: ${color}; flex-shrink: 0; display: inline-block;"></span>
                                    <span style="color: var(--text-high); font-size: 0.75rem;">${arg.event.title}</span>
                               </div>`
                        };
                    }
                });
                calendar.render();

                // Re-theme on dark/light switch
                new MutationObserver(() => {
                    applyFcTheme();
                }).observe(document.documentElement, {
                    attributes: true,
                    attributeFilter: ['data-theme']
                });

                return calendar;
            }

            function applyFcTheme() {
                const style = document.getElementById('fc-dynamic-theme');
                const textHigh = getThemeVar('--text-high');
                const textLow = getThemeVar('--text-low');
                const textMed = getThemeVar('--text-medium');
                const borderMain = getThemeVar('--border-main');
                const borderSub = getThemeVar('--border-subtle');
                const bgInput = getThemeVar('--bg-input');
                const primary = getThemeVar('--primary');

                style.textContent = `
                    .fc { --fc-border-color: ${borderSub}; --fc-page-bg-color: transparent; --fc-event-bg-color: ${primary}; --fc-event-border-color: ${primary}; --fc-today-bg-color: rgba(var(--primary-rgb), 0.04); color: ${textHigh}; }
                    .fc-toolbar-title { font-size: 1.25rem !important; font-weight: 600; color: ${textHigh}; }
                    .fc .fc-button-primary { background-color: ${bgInput}; border: 1px solid ${borderMain}; color: ${textMed}; text-transform: capitalize; font-size: 0.875rem; border-radius: 8px; }
                    .fc .fc-button-primary:hover { background-color: ${primary}; border-color: ${primary}; color: #fff; }
                    .fc .fc-button-primary:not(:disabled).fc-button-active { background-color: ${primary}; border-color: ${primary}; color: #fff; }
                    .fc-theme-bootstrap5 th { background: ${bgInput}; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; padding: 10px !important; color: ${textLow}; letter-spacing: 0.05em; }
                    .fc .fc-daygrid-day-number { color: ${textMed}; font-size: 0.85rem; }
                    .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number { color: ${primary}; font-weight: 700; }
                    .fc .fc-col-header-cell-cushion { color: ${textLow}; }
                    .fc-scrollgrid { border-color: ${borderSub} !important; }
                    .fc-scrollgrid td, .fc-scrollgrid th { border-color: ${borderSub} !important; }
                `;
            }

            document.addEventListener('DOMContentLoaded', function() {
                buildCalendar();
                applyFcTheme();
            });
        </script>
        <style id="fc-dynamic-theme"></style>
    @endpush
</x-admin>
