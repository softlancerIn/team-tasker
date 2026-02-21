<x-admin>
    <x-slot:title>
        Task Calendar | Team Tasker
    </x-slot:title>

    <div class="top-bar mb-4">
        <div class="d-flex align-items-center gap-3">
            <h3 class="mb-0">Task Calendar</h3>
            <div class="ms-3 d-flex gap-2">
                <a href="{{ route('index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-list me-1"></i> List
                </a>
                <a href="{{ route('tasks.board') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-columns me-1"></i> Kanban
                </a>
                <a href="{{ route('tasks.calendar') }}" class="btn btn-outline-secondary btn-sm active">
                    <i class="fas fa-calendar-alt me-1"></i> Calendar
                </a>
            </div>
        </div>
        <a href="{{ route('create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Create Task
        </a>
    </div>

    <div class="glass-card p-4">
        <div id="calendar" style="min-height: 700px;"></div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var calendarEl = document.getElementById('calendar');
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    themeSystem: 'bootstrap5',
                    events: '{{ route('tasks.calendar.events') }}',
                    eventClick: function(info) {
                        window.location.href = '/admin/tasks/details/' + info.event.id;
                    },
                    eventContent: function(arg) {
                        let priority = arg.event.extendedProps.priority;
                        let colorClass = priority === 'Critical' ? 'danger' : (priority === 'High' ?
                            'warning' : 'primary');

                        return {
                            html: `<div class="p-1 px-2 rounded small d-flex align-items-center gap-2 border border-${colorClass} border-opacity-25" style="background: rgba(var(--primary-rgb), 0.1); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <span class="status-dot bg-${colorClass}" style="width: 6px; height: 6px; flex-shrink: 0;"></span>
                                    <span class="text-white">${arg.event.title}</span>
                               </div>`
                        };
                    }
                });
                calendar.render();
            });
        </script>
        <style>
            .fc {
                --fc-border-color: rgba(255, 255, 255, 0.05);
                --fc-page-bg-color: transparent;
                --fc-event-bg-color: var(--primary);
                --fc-event-border-color: var(--primary);
                --fc-today-bg-color: rgba(99, 102, 241, 0.05);
                color: #fff;
            }

            .fc-toolbar-title {
                font-size: 1.25rem !important;
                font-weight: 600;
            }

            .fc .fc-button-primary {
                background-color: rgba(255, 255, 255, 0.05);
                border: 1px solid rgba(255, 255, 255, 0.1);
                color: #fff;
                text-transform: capitalize;
                font-size: 0.875rem;
            }

            .fc .fc-button-primary:hover {
                background-color: var(--primary);
                border-color: var(--primary);
            }

            .fc .fc-button-primary:not(:disabled).fc-button-active {
                background-color: var(--primary);
                border-color: var(--primary);
            }

            .fc-theme-bootstrap5 th {
                background: rgba(255, 255, 255, 0.02);
                font-weight: 500;
                font-size: 0.75rem;
                text-transform: uppercase;
                padding: 10px !important;
                color: #64748b;
            }

            .status-dot {
                border-radius: 50%;
                display: inline-block;
            }
        </style>
    @endpush
</x-admin>
