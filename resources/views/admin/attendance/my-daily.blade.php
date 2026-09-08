<x-admin>
    <x-slot:title>
        My Attendance | Team Tasker
    </x-slot:title>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-high">My Daily Attendance</h3>
            <p class="text-low mb-0" style="font-size: 0.9rem;">Record punches, review daily hours, and submit your report for management approval.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.attendance.myMonthly') }}" class="btn-premium btn-premium-secondary d-flex align-items-center gap-2">
                <i class="fas fa-calendar-alt"></i> View My Monthly Report
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 border-0 mb-4" style="background: rgba(var(--success-rgb), 0.15); color: var(--success);">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center gap-2 border-0 mb-4" style="background: rgba(var(--danger-rgb), 0.15); color: var(--danger);">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
        </div>
    @endif

    <div class="row g-4 mb-4">
        <!-- Today's Punch & Status Card -->
        <div class="col-lg-7">
            <div class="glass-card p-4 border-main h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="text-low small d-block">Today's Date</span>
                            <h4 class="fw-bold text-high mb-0">{{ $today->format('l, d F Y') }}</h4>
                        </div>
                        @if($myAttendance)
                            @php
                                $statusBadge = match($myAttendance->attendance_status) {
                                    'present' => 'success',
                                    'half_day' => 'warning',
                                    'absent' => 'danger',
                                    default => 'secondary',
                                };
                                $appBadge = match($myAttendance->approval_status) {
                                    'approved' => 'success',
                                    'pending' => 'warning',
                                    'rejected' => 'danger',
                                    'draft' => 'info',
                                    default => 'secondary',
                                };
                            @endphp
                            <div class="d-flex gap-2">
                                <span class="badge-premium bg-{{ $statusBadge }}-subtle text-{{ $statusBadge }} border border-{{ $statusBadge }} border-opacity-25 px-3 py-1">
                                    {{ ucfirst(str_replace('_', ' ', $myAttendance->attendance_status)) }}
                                </span>
                                <span class="badge-premium bg-{{ $appBadge }}-subtle text-{{ $appBadge }} border border-{{ $appBadge }} border-opacity-25 px-3 py-1">
                                    {{ ucfirst($myAttendance->approval_status) }}
                                </span>
                            </div>
                        @else
                            <span class="badge-premium bg-secondary-subtle text-low px-3 py-1">Not Punched In</span>
                        @endif
                    </div>

                    <hr class="border-subtle my-3">

                    <div class="row text-center py-3 g-3">
                        <div class="col-4 border-end border-subtle">
                            <span class="text-low small d-block mb-1">Check In</span>
                            <h5 class="fw-bold text-high mb-0">
                                {{ $myAttendance && $myAttendance->check_in ? \Carbon\Carbon::parse($myAttendance->check_in)->format('h:i A') : '--:--' }}
                            </h5>
                        </div>
                        <div class="col-4 border-end border-subtle">
                            <span class="text-low small d-block mb-1">Check Out</span>
                            <h5 class="fw-bold text-high mb-0">
                                {{ $myAttendance && $myAttendance->check_out ? \Carbon\Carbon::parse($myAttendance->check_out)->format('h:i A') : '--:--' }}
                            </h5>
                        </div>
                        <div class="col-4">
                            <span class="text-low small d-block mb-1">Total Time</span>
                            <h5 class="fw-bold text-primary mb-0">
                                {{ $myAttendance ? $myAttendance->formatted_working_hours : '0h 0m' }}
                            </h5>
                        </div>
                    </div>
                </div>

                <!-- Action Punch Buttons -->
                <div class="pt-4 border-top border-subtle d-flex gap-3">
                    @if(! $myAttendance || ! $myAttendance->check_in)
                        <button type="button" class="btn-premium btn-premium-primary flex-grow-1 justify-content-center py-2 fs-6"
                            data-bs-toggle="modal" data-bs-target="#globalClockInModal">
                            <i class="fas fa-sign-in-alt me-2"></i> Punch In Now
                        </button>
                    @elseif(! $myAttendance->check_out)
                        <button type="button" class="btn btn-danger flex-grow-1 justify-content-center py-2 fs-6 d-flex align-items-center"
                            data-bs-toggle="modal" data-bs-target="#globalClockOutModal">
                            <i class="fas fa-sign-out-alt me-2"></i> Punch Out Shift
                        </button>
                    @else
                        <button type="button" class="btn btn-success flex-grow-1 py-2 fs-6" disabled>
                            <i class="fas fa-check-circle me-2"></i> Shift Completed for Today
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Submission & Approval Lifecycle Card -->
        <div class="col-lg-5">
            <div class="glass-card p-4 border-main h-100">
                <h5 class="fw-bold text-high mb-3">Daily Report Submission</h5>

                @if(! $myAttendance)
                    <div class="p-4 text-center text-low border border-dashed border-subtle rounded">
                        <i class="fas fa-clock fa-2x mb-2 text-secondary opacity-50"></i>
                        <p class="mb-0">Punch in above to initiate your daily attendance record.</p>
                    </div>
                @elseif($myAttendance->isApproved())
                    <div class="p-4 rounded border border-success border-opacity-25 bg-success-subtle text-success text-center">
                        <i class="fas fa-badge-check fa-3x mb-2 text-success"></i>
                        <h5 class="fw-bold mb-1">Approved & Locked</h5>
                        <p class="small mb-0">
                            Your attendance for today was approved by <strong>{{ $myAttendance->approver?->name ?? 'Super Admin' }}</strong>
                            at {{ $myAttendance->approved_at ? $myAttendance->approved_at->format('h:i A') : '-' }}.
                        </p>
                    </div>
                @elseif($myAttendance->isPending())
                    <div class="p-4 rounded border border-warning border-opacity-25 bg-warning-subtle text-warning text-center">
                        <i class="fas fa-hourglass-half fa-3x mb-2"></i>
                        <h5 class="fw-bold mb-1">Pending Approval</h5>
                        <p class="small mb-0">
                            Submitted at {{ $myAttendance->submitted_at ? $myAttendance->submitted_at->format('h:i A') : '-' }}.
                            Awaiting Super Admin review.
                        </p>
                    </div>
                @elseif($myAttendance->isRejected())
                    <div class="alert alert-danger border-0 p-3 mb-3">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-exclamation-circle fa-lg mt-1"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Report Rejected by Super Admin</h6>
                                <p class="mb-1 small"><strong>Reason:</strong> {{ $myAttendance->rejection_reason }}</p>
                                <small class="text-low">Please adjust your remarks or clarify details below and resubmit.</small>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.attendance.resubmit', $myAttendance->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-high fw-semibold">Corrected Remarks / Explanation</label>
                            <textarea name="remarks" class="form-premium-control w-100" rows="3" required placeholder="Explain corrections...">{{ $myAttendance->remarks }}</textarea>
                        </div>
                        <button type="submit" class="btn-premium btn-premium-primary w-100 justify-content-center">
                            <i class="fas fa-paper-plane me-2"></i> Resubmit Corrected Report
                        </button>
                    </form>
                @else
                    <!-- Draft state -->
                    <p class="text-low small mb-3">
                        Once you have finished your work or punched out, submit your daily attendance report for management verification and approval.
                    </p>

                    <form action="{{ route('admin.attendance.submit', $myAttendance->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-high fw-semibold">Daily Tasks / Notes (Optional)</label>
                            <textarea name="remarks" class="form-premium-control w-100" rows="3" placeholder="Summary of work completed today...">{{ $myAttendance->remarks }}</textarea>
                        </div>
                        <button type="submit" class="btn-premium btn-premium-primary w-100 justify-content-center">
                            <i class="fas fa-paper-plane me-2"></i> Submit for Approval
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Today's Tracked Tasks & Time Activity -->
    <div class="glass-card p-4 border-main mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold text-high mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-tasks text-success"></i> Today's Task Activity & Timers ({{ $timeLogs->count() }})
            </h5>
            <span class="badge-premium bg-primary-subtle text-primary fw-semibold px-3 py-1">
                Total Tracked: {{ $formattedTotalTracked }}
            </span>
        </div>

        @if($timeLogs->count() > 0)
            <div class="table-responsive">
                <table class="table data-grid-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Project</th>
                            <th>Time Period</th>
                            <th>Duration</th>
                            <th>Mode</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($timeLogs as $tl)
                            <tr>
                                <td>
                                    <a href="{{ route('details', $tl->task_id) }}" class="text-high fw-semibold text-decoration-none">
                                        <i class="fas fa-check-circle text-primary me-1"></i> {{ $tl->task?->title ?? 'Untitled Task' }}
                                    </a>
                                </td>
                                <td class="text-low small">{{ $tl->task?->project?->name ?? 'General' }}</td>
                                <td class="text-low small">{{ $tl->start_time ? $tl->start_time->format('h:i A') : '--' }} - {{ $tl->end_time ? $tl->end_time->format('h:i A') : '--' }}</td>
                                <td>
                                    <span class="badge-premium bg-subtle text-high border-subtle">{{ gmdate('H\h i\m', $tl->duration) }}</span>
                                </td>
                                <td><span class="badge-premium bg-secondary-subtle text-low small">{{ $tl->mode }}</span></td>
                                <td class="text-low small">{{ $tl->description ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4 text-low">
                <i class="fas fa-stopwatch fa-2x mb-2 text-secondary opacity-50"></i>
                <p class="mb-0 small">No tasks logged on timer today. Start a task timer in your task board or details to log work.</p>
            </div>
        @endif
    </div>

    <!-- Attendance Audit Trail / Activity History for User -->
    @if($myAttendance && $myAttendance->logs->count() > 0)
        <div class="glass-card p-4 border-main mb-4">
            <h5 class="fw-bold text-high mb-3 d-flex align-items-center gap-2">
                <i class="fas fa-history text-primary"></i> Today's Activity Trail
            </h5>
            <ul class="list-unstyled mb-0">
                @foreach($myAttendance->logs as $log)
                    <li class="d-flex align-items-start gap-3 py-2 border-bottom border-subtle">
                        <div class="badge-premium bg-primary-subtle text-primary mt-1" style="font-size: 0.75rem;">
                            {{ $log->created_at->format('h:i A') }}
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-high fw-semibold" style="font-size: 0.9rem;">{{ $log->description }}</div>
                            <div class="text-low small">Action: {{ $log->action }} &bull; By: {{ $log->user?->name ?? 'System' }}</div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</x-admin>
