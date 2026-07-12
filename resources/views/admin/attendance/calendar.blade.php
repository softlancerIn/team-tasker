<x-admin>
    <x-slot:title>
        Attendance Calendar | Team Tasker
    </x-slot:title>

    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>

    <div class="top-bar-premium">
        <div>
            <h1 class="h3 fw-semibold mb-1 text-high">Attendance Calendar</h1>
            <p class="text-low mb-0" style="font-size: 0.9rem;">Visual overview of monthly attendance and leave.</p>
        </div>

        @if(Auth::user()->hasPermission('attendance.calendar_all'))
            <form action="{{ route('admin.attendance.calendar') }}" method="GET" class="d-flex gap-2" style="width: 250px;">
                <x-select name="user_id" :options="$users->pluck('name', 'id')->toArray()" :selected="$userId" placeholder="Select User..." onchange="this.form.submit()" class="w-100" />
            </form>
        @endif
    </div>

    <div class="glass-card p-4">
        <div id='calendar' style="min-height: 600px;"></div>
    </div>

    <style>
        .fc-theme-standard td, .fc-theme-standard th {
            border-color: var(--border-subtle);
        }
        .fc-theme-standard .fc-scrollgrid {
            border-color: var(--border-main);
        }
        .fc .fc-daygrid-day.fc-day-today {
            background-color: rgba(var(--primary-rgb), 0.1);
        }
        .fc .fc-toolbar-title {
            color: var(--text-high);
            font-weight: 600;
        }
        .fc .fc-button-primary {
            background-color: var(--bg-input);
            border-color: var(--border-main);
            color: var(--text-high);
            text-transform: capitalize;
        }
        .fc .fc-button-primary:not(:disabled).fc-button-active, 
        .fc .fc-button-primary:not(:disabled):active {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        .fc .fc-button-primary:hover {
            background-color: var(--border-subtle);
        }
        .fc-event {
            cursor: pointer;
            border-radius: 4px;
            padding: 2px 4px;
            font-size: 0.85em;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var events = @json($events);
            
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,dayGridWeek,listWeek'
                },
                events: events,
                height: 'auto',
                eventClick: function(info) {
                    if (info.event.extendedProps && info.event.extendedProps.reason) {
                        document.getElementById('calendarEventTitle').innerText = info.event.title;
                        document.getElementById('calendarEventReason').innerText = info.event.extendedProps.reason;
                        
                        const notesContainer = document.getElementById('calendarEventActionNotesContainer');
                        if (info.event.extendedProps.action_notes) {
                            document.getElementById('calendarEventActionNotes').innerText = info.event.extendedProps.action_notes;
                            notesContainer.classList.remove('d-none');
                        } else {
                            notesContainer.classList.add('d-none');
                        }
                        
                        new bootstrap.Modal(document.getElementById('calendarEventModal')).show();
                    }
                }
            });
            calendar.render();
        });
    </script>

    <!-- Calendar Event Modal -->
    <div class="modal fade" id="calendarEventModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card border-main">
                <div class="modal-header border-subtle">
                    <h5 class="modal-title fw-bold text-high">Leave Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 class="text-high fw-semibold mb-3" id="calendarEventTitle"></h6>
                    <div class="mb-3">
                        <strong class="text-high d-block mb-1">Reason:</strong>
                        <div class="text-medium p-3 bg-dark bg-opacity-10 rounded border border-secondary border-opacity-25" id="calendarEventReason" style="white-space: pre-wrap;"></div>
                    </div>
                    <div id="calendarEventActionNotesContainer" class="d-none">
                        <strong class="text-high d-block mb-1">Action Notes:</strong>
                        <div class="text-medium p-3 bg-dark bg-opacity-10 rounded border border-secondary border-opacity-25" id="calendarEventActionNotes" style="white-space: pre-wrap; color: var(--bs-warning);"></div>
                    </div>
                </div>
                <div class="modal-footer border-subtle">
                    <button type="button" class="btn-premium btn-premium-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</x-admin>
