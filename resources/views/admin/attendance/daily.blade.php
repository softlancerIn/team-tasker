<x-admin>
    <x-slot:title>
        Daily Attendance | Team Tasker
    </x-slot:title>

    <div class="top-bar-premium">
        <div>
            <h1 class="h3 fw-semibold mb-1 text-high">Daily Attendance</h1>
            <p class="text-low mb-0" style="font-size: 0.9rem;">View and manage daily employee attendance.</p>
        </div>
    </div>

    <div class="data-grid-wrapper mb-5">
        <div class="data-grid-top">
            <div class="data-grid-search">
                <form action="{{ route('admin.attendance.daily') }}" method="GET"
                    class="d-flex align-items-center m-0 w-100">
                    <i class="fas fa-search me-2 text-low"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search employees..."
                        class="border-1 bg-transparent text-high w-100" style="outline: none;">
                    @if(request('date'))
                        <input type="hidden" name="date" value="{{ request('date') }}">
                    @endif
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    @if(request('clock_in'))
                        <input type="hidden" name="clock_in" value="{{ request('clock_in') }}">
                    @endif
                    @if(request('clock_out'))
                        <input type="hidden" name="clock_out" value="{{ request('clock_out') }}">
                    @endif
                    @if(request('work_hours'))
                        <input type="hidden" name="work_hours" value="{{ request('work_hours') }}">
                    @endif
                </form>
            </div>
            <div class="data-grid-results">{{ $users->total() }} Results</div>
            <div class="data-grid-actions">
                {{ $users->links() }}
            </div>
        </div>

        <div class="table-responsive">
            <table class="table data-grid-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Status</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Work Hours</th>
                        <th>Location</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        @php
                            $attendance = $attendances->get($user->id);
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-premium" style="width: 32px; height: 32px;">
                                        @if ($user->profile_image)
                                            <img src="{{ asset('storage/' . $user->profile_image) }}" alt="Profile">
                                        @else
                                            {{ substr($user->name ?? 'U', 0, 1) }}
                                        @endif
                                    </div>
                                    <span class="text-high fw-semibold">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td>
                                @if($attendance)
                                    @php
                                        $badgeColor = match ($attendance->status) {
                                            'Present' => 'success',
                                            'Late' => 'warning',
                                            'Half-Day' => 'info',
                                            'Absent' => 'danger',
                                            'Leave' => 'secondary',
                                            'Holiday' => 'primary',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span
                                        class="badge-premium bg-{{ $badgeColor }}-subtle text-{{ $badgeColor }} border border-{{ $badgeColor }} border-opacity-25 px-3 py-1">
                                        {{ $attendance->status }}
                                    </span>
                                @else
                                    <span
                                        class="badge-premium bg-danger-subtle text-danger border border-danger border-opacity-25 px-3 py-1">
                                        Absent
                                    </span>
                                @endif
                            </td>
                            <td class="text-low">
                                {{ $attendance && $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('h:i A') : '--:--' }}
                            </td>
                            <td class="text-low">
                                {{ $attendance && $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('h:i A') : '--:--' }}
                            </td>
                            <td class="text-low">
                                {{ $attendance && $attendance->work_hours ? $attendance->work_hours . ' hrs' : '-' }}
                            </td>
                            <td class="text-low" style="max-width: 250px;">
                                @if($attendance && ($attendance->clock_in_location || $attendance->clock_out_location))
                                    @if($attendance->clock_in_location)
                                        <div class="text-truncate" style="max-width: 230px;"
                                            title="In: {{ $attendance->clock_in_location }}">
                                            <small class="text-success fw-bold me-1">In:</small><i
                                                class="fas fa-map-marker-alt text-primary me-1"></i>{{ $attendance->clock_in_location }}
                                        </div>
                                    @endif
                                    @if($attendance->clock_out_location)
                                        <div class="text-truncate" style="max-width: 230px;"
                                            title="Out: {{ $attendance->clock_out_location }}">
                                            <small class="text-danger fw-bold me-1">Out:</small><i
                                                class="fas fa-map-marker-alt text-danger me-1"></i>{{ $attendance->clock_out_location }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $clockInTime = $attendance && $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '';
                                    $clockOutTime = $attendance && $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '';
                                    $currentStatus = $attendance ? $attendance->status : 'Absent';
                                    $currentNotes = $attendance ? $attendance->notes : '';
                                    $attId = $attendance ? $attendance->id : '';
                                @endphp
                                <button class="btn btn-sm btn-premium-secondary" title="Edit"
                                    onclick="openEditModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ request('date', $date) }}', '{{ $attId }}', '{{ $currentStatus }}', '{{ $clockInTime }}', '{{ $clockOutTime }}', '{{ addslashes($currentNotes) }}', '{{ addslashes($attendance->clock_in_location ?? 'N/A') }}', '{{ addslashes($attendance->clock_out_location ?? 'N/A') }}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin>

<div class="filter-slideover" id="filterSlideoverAttendance">
    <form action="{{ route('admin.attendance.daily') }}" method="GET" class="h-100 d-flex flex-column">
        <div class="filter-slideover-header">
            <h4><i class="fas fa-sliders-h text-low me-2"></i> Advanced Filters</h4>
            <div class="filter-slideover-close"
                onclick="document.querySelector('.filter-slideover').classList.remove('show')">
                <i class="fas fa-times"></i>
            </div>
        </div>
        <div class="filter-slideover-body">
            <div class="mb-4">
                <label class="heading-label d-block mb-2 text-low">DATE</label>
                <input type="date" name="date" value="{{ request('date', $date) }}"
                    class="form-premium-control bg-white text-dark border-main">
            </div>
            <div class="mb-4">
                <label class="heading-label d-block mb-2 text-low">STATUS</label>
                <select name="status" class="form-premium-control bg-white text-dark border-main">
                    <option value="">All</option>
                    <option value="Present" {{ request('status') == 'Present' ? 'selected' : '' }}>Present</option>
                    <option value="Late" {{ request('status') == 'Late' ? 'selected' : '' }}>Late</option>
                    <option value="Half-Day" {{ request('status') == 'Half-Day' ? 'selected' : '' }}>Half-Day</option>
                    <option value="Absent" {{ request('status') == 'Absent' ? 'selected' : '' }}>Absent</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="heading-label d-block mb-2 text-low">CLOCK IN (AFTER)</label>
                <input type="time" name="clock_in" value="{{ request('clock_in') }}"
                    class="form-premium-control bg-white text-dark border-main">
            </div>
            <div class="mb-4">
                <label class="heading-label d-block mb-2 text-low">CLOCK OUT (BEFORE)</label>
                <input type="time" name="clock_out" value="{{ request('clock_out') }}"
                    class="form-premium-control bg-white text-dark border-main">
            </div>
            <div class="mb-4">
                <label class="heading-label d-block mb-2 text-low">WORK HOURS (MINIMUM)</label>
                <input type="number" step="0.1" name="work_hours" value="{{ request('work_hours') }}"
                    placeholder="e.g. 8" class="form-premium-control bg-white text-dark border-main">
            </div>
        </div>
        <div class="filter-slideover-footer">
            <a href="{{ route('admin.attendance.daily') }}"
                class="btn-premium btn-premium-secondary w-50 justify-content-center bg-white text-dark border-main">Reset</a>
            <button type="submit" class="btn-premium btn-premium-primary w-50 justify-content-center"
                style="background: #0ea5e9;">Apply Filters</button>
        </div>
    </form>
</div>

<!-- Edit Attendance Modal -->
<div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content premium-modal">
            <div class="modal-header border-subtle">
                <h5 class="modal-title fw-bold text-high">Edit Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.attendance.daily.update') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" id="edit_user_id">
                <input type="hidden" name="date" id="edit_date">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-high fw-semibold">Employee</label>
                        <input type="text" id="edit_user_name" class="form-premium-control bg-subtle" disabled>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-high fw-semibold">Clock In Location</label>
                            <input type="text" id="edit_clock_in_location" class="form-premium-control bg-subtle" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-high fw-semibold">Clock Out Location</label>
                            <input type="text" id="edit_clock_out_location" class="form-premium-control bg-subtle" disabled>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-high fw-semibold">Status</label>
                        <select name="status" id="edit_status" class="form-premium-control w-100" required>
                            <option value="Present">Present</option>
                            <option value="Late">Late</option>
                            <option value="Half-Day">Half-Day</option>
                            <option value="Absent">Absent</option>
                            <option value="Leave">Leave</option>
                            <option value="Holiday">Holiday</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-high fw-semibold">Clock In</label>
                            <input type="time" name="clock_in" id="edit_clock_in" class="form-premium-control w-100">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-high fw-semibold">Clock Out</label>
                            <input type="time" name="clock_out" id="edit_clock_out" class="form-premium-control w-100">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-high fw-semibold">Notes</label>
                        <textarea name="notes" id="edit_notes" class="form-premium-control w-100" rows="3"
                            placeholder="Add any admin notes here..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-subtle">
                    <button type="button" class="btn-premium btn-premium-secondary"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-premium btn-premium-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditModal(userId, userName, date, attId, status, clockIn, clockOut, notes, clockInLocation, clockOutLocation) {
        document.getElementById('edit_user_id').value = userId;
        document.getElementById('edit_user_name').value = userName + ' (' + date + ')';
        document.getElementById('edit_clock_in_location').value = clockInLocation || 'N/A';
        document.getElementById('edit_clock_out_location').value = clockOutLocation || 'N/A';
        document.getElementById('edit_date').value = date;

        document.getElementById('edit_status').value = status || 'Absent';
        document.getElementById('edit_clock_in').value = clockIn;
        document.getElementById('edit_clock_out').value = clockOut;
        document.getElementById('edit_notes').value = notes;

        var editModal = new bootstrap.Modal(document.getElementById('editAttendanceModal'));
        editModal.show();
    }
</script>