<x-admin>
    <x-slot:title>
        Employee Profile & Performance: {{ $user->name }} | Team Tasker
    </x-slot:title>

    <style>
        .emp-profile-header {
            background: var(--bg-surface);
            border: 1px solid var(--border-main);
            border-radius: var(--radius-lg, 12px);
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .emp-profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #0284c7, #38bdf8, #818cf8);
        }

        .emp-avatar-lg {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            border: 3px solid rgba(var(--primary-rgb, 14, 165, 233), 0.25);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            font-weight: 700;
            background: rgba(var(--primary-rgb, 14, 165, 233), 0.1);
            color: var(--primary, #0ea5e9);
            flex-shrink: 0;
            overflow: hidden;
        }

        .emp-avatar-lg img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .stat-badge-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-main);
            border-radius: var(--radius-md, 10px);
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 100%;
        }

        .nav-tabs-custom {
            border-bottom: 1px solid var(--border-main);
            gap: 0.5rem;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            color: var(--text-low);
            font-weight: 600;
            font-size: 0.88rem;
            padding: 0.75rem 1.25rem;
            background: transparent;
            border-radius: 0;
            transition: all 0.2s ease;
        }

        .nav-tabs-custom .nav-link:hover {
            color: var(--text-high);
            border-bottom-color: var(--border-main);
        }

        .nav-tabs-custom .nav-link.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
            background: transparent;
        }
    </style>

    <div class="container-fluid py-3">

        {{-- Top Navigation & Actions --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.users.index') }}" class="btn-premium btn-premium-secondary d-flex align-items-center gap-2 shadow-sm">
                    <i class="fas fa-arrow-left"></i> Team Directory
                </a>
                <a href="{{ route('admin.payroll.salaries') }}" class="btn-premium btn-premium-secondary d-flex align-items-center gap-2 shadow-sm">
                    <i class="fas fa-wallet"></i> Salary Directory
                </a>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.attendance.reports', ['export' => 'single', 'user_id' => $user->id, 'month' => now()->format('Y-m')]) }}"
                   class="btn-premium btn-premium-secondary d-flex align-items-center gap-2 shadow-sm"
                   title="Download Monthly Attendance CSV">
                    <i class="fas fa-file-csv text-success"></i> Export Attendance
                </a>
                <a href="{{ route('admin.users.index', ['name' => $user->name]) }}"
                   class="btn-premium btn-premium-primary d-flex align-items-center gap-2 shadow-sm">
                    <i class="fas fa-user-edit"></i> Edit User Info
                </a>
            </div>
        </div>

        {{-- Profile Header Card --}}
        <div class="emp-profile-header mb-4">
            <div class="row g-4 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center gap-3">
                        <div class="emp-avatar-lg">
                            @if ($user->profile_image)
                            <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}">
                            @else
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                            @endif
                        </div>

                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <h1 class="h3 fw-bold text-high mb-0">{{ $user->name }}</h1>
                                @if($user->is_payable)
                                <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #10b981; font-weight: 700; font-size: 0.72rem; padding: 4px 8px; border-radius: 6px;">
                                    <i class="fas fa-check-circle me-1"></i> PAYABLE EMPLOYEE
                                </span>
                                @else
                                <span class="badge" style="background: rgba(148, 163, 184, 0.15); color: #94a3b8; font-weight: 600; font-size: 0.72rem; padding: 4px 8px; border-radius: 6px;">
                                    <i class="fas fa-ban me-1"></i> NON-PAYABLE
                                </span>
                                @endif

                                @if($user->is_approved)
                                <span class="badge" style="background: rgba(14, 165, 233, 0.12); color: #0ea5e9; font-weight: 700; font-size: 0.72rem; padding: 4px 8px; border-radius: 6px;">
                                    APPROVED
                                </span>
                                @else
                                <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; font-weight: 700; font-size: 0.72rem; padding: 4px 8px; border-radius: 6px;">
                                    PENDING APPROVAL
                                </span>
                                @endif
                            </div>

                            <p class="text-low mb-2" style="font-size: 0.88rem;">
                                <i class="fas fa-briefcase me-1 text-primary"></i> {{ $user->designation ?? 'Team Member' }}
                                @if($user->department) &bull; {{ $user->department }} @endif
                                &bull; <i class="fas fa-shield-alt ms-1 text-info"></i> {{ $user->role?->name ?? 'Normal User' }}
                            </p>

                            <div class="d-flex flex-wrap align-items-center gap-3 text-low" style="font-size: 0.82rem;">
                                <span><i class="fas fa-envelope me-1 text-primary"></i> {{ $user->email }}</span>
                                @if($user->phone)
                                <span><i class="fas fa-phone me-1 text-success"></i> {{ $user->phone }}</span>
                                @endif
                                @if($user->employee_code)
                                <span><i class="fas fa-id-badge me-1 text-warning"></i> Code: <strong>{{ $user->employee_code }}</strong></span>
                                @endif
                                @if($user->joining_date)
                                <span><i class="fas fa-calendar-check me-1 text-info"></i> Joined: {{ $user->joining_date->format('d M Y') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="p-3 rounded border-main" style="background: rgba(var(--primary-rgb, 14, 165, 233), 0.04);">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-low small fw-semibold">Compensation Package</span>
                            <span class="badge bg-primary-subtle text-primary">
                                {{ $user->currentSalary ? 'Active Package' : 'Not Configured' }}
                            </span>
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mb-1">
                            <h2 class="h3 fw-bold text-high mb-0">
                                @if($user->currentSalary)
                                ₹{{ number_format((float) $user->currentSalary->monthly_salary, 2) }}
                                @else
                                <span class="text-low fst-italic">No Salary Set</span>
                                @endif
                            </h2>
                            @if($user->currentSalary)
                            <span class="text-low small">/ month</span>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between align-items-center text-low small mt-2 pt-2 border-top border-subtle">
                            @php
                            $workDays = $user->currentSalary ? (array) $user->currentSalary->working_days : [];
                            @endphp
                            <span>Working: <strong>{{ count(array_unique($workDays)) }} days/week</strong></span>
                            <span>Effective: {{ $user->currentSalary?->effective_from?->format('d M Y') ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Key Stats Overview --}}
        <div class="row row-cols-2 row-cols-md-4 g-3 mb-4">
            <div class="col">
                <div class="stat-badge-card">
                    <div>
                        <span class="text-low d-block small mb-1">Total Attendance Days</span>
                        <h4 class="fw-bold mb-0 text-high">{{ number_format($totalAttendanceCount) }}</h4>
                        <span class="text-success extra-small">
                            <i class="fas fa-check-circle me-1"></i>{{ $presentCount }} Present &bull; {{ $halfDayCount }} Half-Day
                        </span>
                    </div>
                    <div class="rounded p-2 text-primary d-inline-flex" style="background: rgba(var(--primary-rgb, 14, 165, 233), 0.12);">
                        <i class="fas fa-calendar-check fs-5"></i>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="stat-badge-card">
                    <div>
                        <span class="text-low d-block small mb-1">Late Arrivals</span>
                        <h4 class="fw-bold mb-0 text-warning">{{ number_format($lateCount) }}</h4>
                        <span class="text-low extra-small">Recorded late check-ins</span>
                    </div>
                    <div class="rounded p-2 text-warning d-inline-flex" style="background: rgba(245, 158, 11, 0.12);">
                        <i class="fas fa-clock fs-5"></i>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="stat-badge-card">
                    <div>
                        <span class="text-low d-block small mb-1">Work Reports Submitted</span>
                        <h4 class="fw-bold mb-0 text-info">{{ number_format($reportsSubmittedCount) }}</h4>
                        <span class="text-low extra-small">Daily task & work reports</span>
                    </div>
                    <div class="rounded p-2 text-info d-inline-flex" style="background: rgba(6, 182, 212, 0.12);">
                        <i class="fas fa-file-alt fs-5"></i>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="stat-badge-card">
                    <div>
                        <span class="text-low d-block small mb-1">Tasks Completed</span>
                        <h4 class="fw-bold mb-0 text-success">{{ number_format($completedTasksCount) }} / {{ number_format($totalAssignedTasks) }}</h4>
                        <span class="text-low extra-small">
                            {{ $totalAssignedTasks > 0 ? round(($completedTasksCount / $totalAssignedTasks) * 100) : 0 }}% completion rate
                        </span>
                    </div>
                    <div class="rounded p-2 text-success d-inline-flex" style="background: rgba(16, 185, 129, 0.12);">
                        <i class="fas fa-tasks fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Visual Graph Section: Attendance & Report Submission History --}}
        <div class="glass-card mb-4 p-4 border-main">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h5 class="fw-bold text-high mb-1">
                        <i class="fas fa-chart-line text-primary me-2"></i> Attendance & Work Report Analytics (Last 30 Days)
                    </h5>
                    <p class="text-low mb-0 small">
                        Daily attendance check-ins, punctuality records, and work report submission timeline.
                    </p>
                </div>
                <div class="d-flex align-items-center gap-3 small">
                    <span class="d-inline-flex align-items-center gap-1 text-high">
                        <span style="width: 12px; height: 12px; border-radius: 3px; background: #10b981;"></span> Present
                    </span>
                    <span class="d-inline-flex align-items-center gap-1 text-high">
                        <span style="width: 12px; height: 12px; border-radius: 3px; background: #f59e0b;"></span> Late
                    </span>
                    <span class="d-inline-flex align-items-center gap-1 text-high">
                        <span style="width: 12px; height: 12px; border-radius: 3px; background: #ef4444;"></span> Absent
                    </span>
                    <span class="d-inline-flex align-items-center gap-1 text-high">
                        <span style="width: 12px; height: 12px; border-radius: 3px; background: #0ea5e9;"></span> Daily Report
                    </span>
                </div>
            </div>

            <div style="height: 260px; position: relative;">
                <canvas id="userAttendanceReportChart"></canvas>
            </div>
        </div>

        {{-- Nav Tabs for Detailed Breakdown --}}
        <ul class="nav nav-tabs nav-tabs-custom mb-4" id="userTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#tab-attendance" type="button" role="tab">
                    <i class="fas fa-calendar-alt me-1"></i> Attendance Log ({{ $attendances->total() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="reports-tab" data-bs-toggle="tab" data-bs-target="#tab-reports" type="button" role="tab">
                    <i class="fas fa-file-signature me-1"></i> Work Reports History ({{ $reports->total() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tab-tasks" type="button" role="tab">
                    <i class="fas fa-list-check me-1"></i> Assigned Tasks ({{ $tasks->total() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="payroll-tab" data-bs-toggle="tab" data-bs-target="#tab-payroll" type="button" role="tab">
                    <i class="fas fa-file-invoice-dollar me-1"></i> Payroll & Salary History
                </button>
            </li>
        </ul>

        {{-- Tab Contents --}}
        <div class="tab-content" id="userTabContents">

            {{-- 1. Attendance History Tab --}}
            <div class="tab-pane fade show active" id="tab-attendance" role="tabpanel">
                <div class="glass-card p-0 overflow-hidden border-main">
                    <div class="table-responsive">
                        <table class="table data-grid-table mb-0">
                            <thead>
                                <tr>
                                    <th>DATE</th>
                                    <th>CLOCK IN</th>
                                    <th>CLOCK OUT</th>
                                    <th>WORK DURATION</th>
                                    <th>STATUS</th>
                                    <th>APPROVAL</th>
                                    <th>REMARKS / WORK NOTES</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $att)
                                <tr>
                                    <td class="text-nowrap fw-semibold text-high">
                                        {{ \Carbon\Carbon::parse($att->date ?? $att->attendance_date)->format('D, d M Y') }}
                                    </td>
                                    <td>
                                        {{ $att->clock_in ? \Carbon\Carbon::parse($att->clock_in)->format('h:i A') : '-' }}
                                    </td>
                                    <td>
                                        {{ $att->clock_out ? \Carbon\Carbon::parse($att->clock_out)->format('h:i A') : '-' }}
                                    </td>
                                    <td>
                                        <span class="badge-premium bg-light text-dark fw-bold" style="font-size: 0.78rem;">
                                            {{ $att->formatted_working_hours ?? ($att->work_hours ? $att->work_hours . ' hrs' : '-') }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                        $st = strtolower($att->status ?? $att->attendance_status ?? 'present');
                                        @endphp
                                        @if(in_array($st, ['present']))
                                        <span class="badge bg-success-subtle text-success fw-bold px-2 py-1">PRESENT</span>
                                        @elseif(in_array($st, ['late']))
                                        <span class="badge bg-warning-subtle text-warning fw-bold px-2 py-1">LATE</span>
                                        @elseif(in_array($st, ['absent']))
                                        <span class="badge bg-danger-subtle text-danger fw-bold px-2 py-1">ABSENT</span>
                                        @elseif(in_array($st, ['half-day', 'half_day']))
                                        <span class="badge bg-info-subtle text-info fw-bold px-2 py-1">HALF-DAY</span>
                                        @else
                                        <span class="badge bg-secondary-subtle text-secondary fw-bold px-2 py-1">{{ strtoupper($st) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($att->approval_status === 'approved')
                                        <span class="text-success small fw-bold"><i class="fas fa-check-circle me-1"></i>Approved</span>
                                        @elseif($att->approval_status === 'rejected')
                                        <span class="text-danger small fw-bold"><i class="fas fa-times-circle me-1"></i>Rejected</span>
                                        @else
                                        <span class="text-warning small fw-bold"><i class="fas fa-clock me-1"></i>Pending</span>
                                        @endif
                                    </td>
                                    <td style="max-width: 250px;">
                                        @if($att->remarks)
                                        <span class="text-high small text-truncate d-block" title="{{ $att->remarks }}">
                                            {{ $att->remarks }}
                                        </span>
                                        @else
                                        <span class="text-low extra-small fst-italic">No remarks</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-low">
                                        <i class="fas fa-calendar-times fs-4 mb-2 d-block text-low"></i>
                                        No attendance logs found for this employee.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($attendances->hasPages())
                    <div class="card-footer p-3 border-top border-subtle">
                        {{ $attendances->links() }}
                    </div>
                    @endif
                </div>
            </div>

            {{-- 2. Daily Work Reports Tab --}}
            <div class="tab-pane fade" id="tab-reports" role="tabpanel">
                <div class="glass-card p-0 overflow-hidden border-main">
                    <div class="table-responsive">
                        <table class="table data-grid-table mb-0">
                            <thead>
                                <tr>
                                    <th>SUBMISSION DATE</th>
                                    <th>WORK REPORT DESCRIPTION / REMARKS</th>
                                    <th>HOURS LOGGED</th>
                                    <th>APPROVAL STATUS</th>
                                    <th>ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports as $rep)
                                <tr>
                                    <td class="text-nowrap fw-semibold text-high">
                                        <i class="fas fa-calendar-day text-primary me-1"></i>
                                        {{ \Carbon\Carbon::parse($rep->date ?? $rep->attendance_date)->format('D, d M Y') }}
                                    </td>
                                    <td>
                                        <div class="p-2 rounded border-subtle" style="background: rgba(255, 255, 255, 0.03); max-width: 580px; white-space: pre-wrap; font-size: 0.85rem; line-height: 1.45;">
                                            {{ $rep->remarks }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-premium bg-light text-dark fw-bold">
                                            {{ $rep->formatted_working_hours ?? ($rep->work_hours ? $rep->work_hours . ' hrs' : '-') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($rep->approval_status === 'approved')
                                        <span class="badge bg-success-subtle text-success fw-bold px-2 py-1">APPROVED</span>
                                        @elseif($rep->approval_status === 'rejected')
                                        <span class="badge bg-danger-subtle text-danger fw-bold px-2 py-1">REJECTED</span>
                                        @else
                                        <span class="badge bg-warning-subtle text-warning fw-bold px-2 py-1">PENDING</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.attendance.daily', ['date' => \Carbon\Carbon::parse($rep->date)->format('Y-m-d')]) }}"
                                           class="btn btn-sm btn-outline-primary py-1 px-2"
                                           title="View Daily Attendance Sheet">
                                            <i class="fas fa-external-link-alt me-1"></i> Sheet
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-low">
                                        <i class="fas fa-file-alt fs-4 mb-2 d-block text-low"></i>
                                        No daily work reports submitted by this user.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($reports->hasPages())
                    <div class="card-footer p-3 border-top border-subtle">
                        {{ $reports->links() }}
                    </div>
                    @endif
                </div>
            </div>

            {{-- 3. Assigned Tasks Tab --}}
            <div class="tab-pane fade" id="tab-tasks" role="tabpanel">
                <div class="glass-card p-0 overflow-hidden border-main">
                    <div class="table-responsive">
                        <table class="table data-grid-table mb-0">
                            <thead>
                                <tr>
                                    <th>TASK TITLE</th>
                                    <th>PROJECT</th>
                                    <th>STATUS</th>
                                    <th>DUE DATE</th>
                                    <th class="text-end">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tasks as $task)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-high">{{ $task->title }}</div>
                                        <span class="text-low extra-small">#{{ $task->id }}</span>
                                    </td>
                                    <td>
                                        <span class="text-high small">{{ $task->project?->name ?? 'General' }}</span>
                                    </td>
                                    <td>
                                        @if($task->status)
                                        <span class="badge" style="background: {{ $task->status->color ?? 'rgba(14, 165, 233, 0.2)' }}; color: #fff; font-size: 0.72rem;">
                                            {{ $task->status->name }}
                                        </span>
                                        @else
                                        <span class="badge bg-secondary">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap small text-low">
                                        {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d M Y') : 'No Deadline' }}
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('details', $task->id) }}" class="btn btn-sm btn-outline-info py-1 px-2" target="_blank">
                                            <i class="fas fa-eye me-1"></i> Details
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-low">
                                        <i class="fas fa-tasks fs-4 mb-2 d-block text-low"></i>
                                        No tasks assigned to this employee.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($tasks->hasPages())
                    <div class="card-footer p-3 border-top border-subtle">
                        {{ $tasks->links() }}
                    </div>
                    @endif
                </div>
            </div>

            {{-- 4. Payroll & Salary History Tab --}}
            <div class="tab-pane fade" id="tab-payroll" role="tabpanel">
                <div class="glass-card p-4 border-main mb-4">
                    <h5 class="fw-bold text-high mb-3"><i class="fas fa-history text-primary me-2"></i> Salary Structure History</h5>
                    <div class="table-responsive">
                        <table class="table data-grid-table mb-0">
                            <thead>
                                <tr>
                                    <th>EFFECTIVE FROM</th>
                                    <th>EFFECTIVE UNTIL</th>
                                    <th>MONTHLY SALARY</th>
                                    <th>WORKING DAYS</th>
                                    <th>STATUS</th>
                                    <th>NOTES</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($user->salaries as $sal)
                                <tr>
                                    <td class="text-nowrap fw-semibold text-high">
                                        {{ \Carbon\Carbon::parse($sal->effective_from)->format('d M Y') }}
                                    </td>
                                    <td class="text-nowrap text-low">
                                        {{ $sal->effective_until ? \Carbon\Carbon::parse($sal->effective_until)->format('d M Y') : 'Ongoing' }}
                                    </td>
                                    <td class="text-high fw-bold">
                                        ₹{{ number_format((float) $sal->monthly_salary, 2) }}
                                    </td>
                                    <td>
                                        @php
                                        $d = is_string($sal->working_days) ? json_decode($sal->working_days, true) : (array) $sal->working_days;
                                        @endphp
                                        <span class="badge bg-light text-dark fw-bold">{{ count(array_unique((array) $d)) }} days / week</span>
                                    </td>
                                    <td>
                                        @if($sal->is_active)
                                        <span class="badge bg-success-subtle text-success fw-bold px-2 py-1">ACTIVE</span>
                                        @else
                                        <span class="badge bg-secondary-subtle text-secondary fw-bold px-2 py-1">REPLACED</span>
                                        @endif
                                    </td>
                                    <td class="text-low small">{{ $sal->notes ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-3 text-low">No salary records configured for this user.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Payroll Batches History --}}
                <div class="glass-card p-4 border-main">
                    <h5 class="fw-bold text-high mb-3"><i class="fas fa-receipt text-success me-2"></i> Generated Payrolls</h5>
                    <div class="table-responsive">
                        <table class="table data-grid-table mb-0">
                            <thead>
                                <tr>
                                    <th>PAYROLL MONTH</th>
                                    <th>PRESENT / HALF DAYS</th>
                                    <th class="text-end">GROSS SALARY</th>
                                    <th class="text-end">DEDUCTIONS</th>
                                    <th class="text-end">NET SALARY</th>
                                    <th>STATUS</th>
                                    <th class="text-end">PAYSLIP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($user->payrolls as $pay)
                                <tr>
                                    <td class="fw-semibold text-high">
                                        {{ \Carbon\Carbon::parse($pay->payroll_month)->format('F Y') }}
                                    </td>
                                    <td>
                                        {{ $pay->present_days }} Present &bull; {{ $pay->half_days }} Half-Day
                                    </td>
                                    <td class="text-end text-high">
                                        ₹{{ number_format((float) $pay->gross_salary, 2) }}
                                    </td>
                                    <td class="text-end text-danger">
                                        -₹{{ number_format((float) ($pay->deduction_amount + $pay->other_deductions), 2) }}
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        ₹{{ number_format((float) $pay->final_salary, 2) }}
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase px-2 py-1">
                                            {{ $pay->status }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.payroll.show', $pay->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2">
                                            <i class="fas fa-file-invoice me-1"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-3 text-low">No generated payrolls found for this user.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

    </div>

    {{-- Chart.js integration for User Attendance & Reports Graph --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('userAttendanceReportChart');
            if (ctx) {
                const labels = {!! json_encode($chartLabels) !!};
                const presentData = {!! json_encode($chartPresent) !!};
                const lateData = {!! json_encode($chartLate) !!};
                const absentData = {!! json_encode($chartAbsent) !!};
                const reportData = {!! json_encode($chartReportCount) !!};

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Present',
                                data: presentData,
                                backgroundColor: '#10b981',
                                stack: 'attendance',
                                borderRadius: 4,
                            },
                            {
                                label: 'Late',
                                data: lateData,
                                backgroundColor: '#f59e0b',
                                stack: 'attendance',
                                borderRadius: 4,
                            },
                            {
                                label: 'Absent',
                                data: absentData,
                                backgroundColor: '#ef4444',
                                stack: 'attendance',
                                borderRadius: 4,
                            },
                            {
                                label: 'Daily Report Submitted',
                                data: reportData,
                                type: 'line',
                                borderColor: '#0ea5e9',
                                backgroundColor: 'rgba(14, 165, 233, 0.15)',
                                borderWidth: 2,
                                pointRadius: 3,
                                fill: true,
                                tension: 0.3,
                                yAxisID: 'yReport',
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false,
                                    drawBorder: false,
                                },
                                ticks: {
                                    font: { size: 10 },
                                    color: '#94a3b8',
                                    maxRotation: 45,
                                    autoSkip: true,
                                    maxTicksLimit: 15
                                }
                            },
                            y: {
                                stacked: true,
                                min: 0,
                                max: 1.2,
                                display: false,
                            },
                            yReport: {
                                type: 'linear',
                                display: false,
                                min: 0,
                                max: 1.2,
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                titleColor: '#f8fafc',
                                bodyColor: '#cbd5e1',
                                borderColor: '#334155',
                                borderWidth: 1,
                                padding: 10,
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        let val = context.raw;
                                        if (val === 1) {
                                            return label + ': Yes';
                                        }
                                        return label + ': No';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-admin>
