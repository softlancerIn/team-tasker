<x-admin>
    <x-slot:title>
        My Monthly Report | Team Tasker
    </x-slot:title>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-high">My Monthly Attendance</h3>
            <p class="text-low mb-0" style="font-size: 0.9rem;">Review your attendance history, monthly working hours, and calendar breakdown.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.attendance.myDaily') }}" class="btn-premium btn-premium-secondary d-flex align-items-center gap-2">
                <i class="fas fa-calendar-day"></i> Today's Attendance
            </a>
        </div>
    </div>

    <!-- Month Selector Bar -->
    <div class="glass-card p-3 mb-4 border-main">
        <form action="{{ route('admin.attendance.myMonthly') }}" method="GET" class="d-flex align-items-center gap-3 m-0 flex-wrap">
            <label class="form-label text-high fw-semibold mb-0">Select Month:</label>
            <input type="month" name="month" class="form-premium-control" style="max-width: 200px;" value="{{ $month }}" onchange="this.form.submit()">
            
            @php
                $currentMonthStr = now()->format('Y-m');
                $prevMonthStr = now()->subMonth()->format('Y-m');
            @endphp
            <div class="d-flex gap-2 ms-auto">
                <a href="{{ route('admin.attendance.myMonthly', ['month' => $currentMonthStr]) }}" 
                   class="btn btn-sm {{ $month === $currentMonthStr ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Current Month
                </a>
                <a href="{{ route('admin.attendance.myMonthly', ['month' => $prevMonthStr]) }}" 
                   class="btn btn-sm {{ $month === $prevMonthStr ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Previous Month
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="row row-cols-2 row-cols-md-4 row-cols-xl-7 g-3 mb-4 text-center">
        <div class="col">
            <div class="glass-card p-3 border-main h-100">
                <span class="text-low small d-block">Present</span>
                <h4 class="fw-bold text-success mb-0">{{ $stats['present_days'] }}</h4>
            </div>
        </div>
        <div class="col">
            <div class="glass-card p-3 border-main h-100">
                <span class="text-low small d-block">Half Day</span>
                <h4 class="fw-bold text-warning mb-0">{{ $stats['half_days'] }}</h4>
            </div>
        </div>
        <div class="col">
            <div class="glass-card p-3 border-main h-100">
                <span class="text-low small d-block">Absent</span>
                <h4 class="fw-bold text-danger mb-0">{{ $stats['absent_days'] }}</h4>
            </div>
        </div>
        <div class="col">
            <div class="glass-card p-3 border-main h-100">
                <span class="text-low small d-block">Pending Approval</span>
                <h4 class="fw-bold text-warning mb-0">{{ $stats['pending_days'] }}</h4>
            </div>
        </div>
        <div class="col">
            <div class="glass-card p-3 border-main h-100">
                <span class="text-low small d-block">Rejected</span>
                <h4 class="fw-bold text-danger mb-0">{{ $stats['rejected_days'] }}</h4>
            </div>
        </div>
        <div class="col">
            <div class="glass-card p-3 border-main h-100">
                <span class="text-low small d-block">Total Working Hours</span>
                <h4 class="fw-bold text-primary mb-0">{{ $stats['formatted_working_hours'] }}</h4>
            </div>
        </div>
        <div class="col">
            <div class="glass-card p-3 border-main h-100">
                <span class="text-low small d-block">Attendance %</span>
                <h4 class="fw-bold text-info mb-0">{{ $stats['attendance_percentage'] }}%</h4>
            </div>
        </div>
    </div>

    <!-- Monthly Calendar Matrix -->
    <div class="glass-card p-4 mb-4 border-main">
        <h5 class="fw-bold text-high mb-3 d-flex align-items-center gap-2">
            <i class="fas fa-calendar-alt text-primary"></i> {{ \Carbon\Carbon::parse($month.'-01')->format('F Y') }} Calendar
        </h5>

        <div class="d-flex flex-wrap gap-2 mb-3">
            @foreach($calendar as $cDay)
                <div class="p-2 rounded text-center border {{ $cDay['is_weekend'] ? 'bg-subtle border-subtle' : 'border-main' }}" 
                     style="min-width: 48px; flex: 1 0 45px;"
                     title="{{ $cDay['date'] }}: {{ $cDay['label'] }}">
                    <div class="text-low" style="font-size: 0.65rem;">{{ $cDay['day_name'] }}</div>
                    <div class="fw-bold small">{{ $cDay['day'] }}</div>
                    <span class="badge bg-{{ $cDay['class'] }} d-block mt-1 py-1" style="font-size: 0.7rem;">
                        {{ $cDay['badge'] }}
                    </span>
                </div>
            @endforeach
        </div>

        <div class="d-flex align-items-center gap-3 text-low small">
            <span><span class="badge bg-success me-1">P</span> Present</span>
            <span><span class="badge bg-warning me-1">H</span> Half Day</span>
            <span><span class="badge bg-danger me-1">A</span> Absent</span>
            <span><span class="badge bg-secondary me-1">-</span> No Record</span>
        </div>
    </div>

    <!-- Daily Records Breakdown Table -->
    <div class="data-grid-wrapper mb-4">
        <div class="p-3 border-bottom border-subtle">
            <h6 class="fw-bold text-high mb-0">Daily Records ({{ \Carbon\Carbon::parse($month.'-01')->format('F Y') }})</h6>
        </div>
        <div class="table-responsive">
            <table class="table data-grid-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Working Hours</th>
                        <th>Attendance Status</th>
                        <th>Approval Status</th>
                        <th>Remarks / Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @php $hasRecords = false; @endphp
                    @foreach($calendar as $cDay)
                        @if($cDay['attendance'])
                            @php
                                $hasRecords = true;
                                $att = $cDay['attendance'];
                            @endphp
                            <tr>
                                <td class="text-high fw-semibold">
                                    {{ \Carbon\Carbon::parse($cDay['date'])->format('d M Y (D)') }}
                                </td>
                                <td class="text-high">
                                    {{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('h:i A') : '--:--' }}
                                </td>
                                <td class="text-high">
                                    {{ $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('h:i A') : '--:--' }}
                                </td>
                                <td class="text-low">
                                    <span class="badge-premium bg-subtle text-high border-subtle">
                                        {{ $att->formatted_working_hours }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-premium bg-{{ $cDay['class'] }}-subtle text-{{ $cDay['class'] }} px-2 py-1">
                                        {{ ucfirst(str_replace('_', ' ', $att->attendance_status)) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $appBadge = match($att->approval_status) {
                                            'approved' => 'success',
                                            'pending' => 'warning',
                                            'rejected' => 'danger',
                                            default => 'info',
                                        };
                                    @endphp
                                    <span class="badge-premium bg-{{ $appBadge }}-subtle text-{{ $appBadge }} px-2 py-1">
                                        {{ ucfirst($att->approval_status) }}
                                    </span>
                                    @if($att->isRejected() && $att->rejection_reason)
                                        <div class="text-danger small mt-1">Reason: {{ $att->rejection_reason }}</div>
                                    @endif
                                </td>
                                <td class="text-low small">
                                    {{ $att->remarks ?: '-' }}
                                </td>
                            </tr>
                        @endif
                    @endforeach

                    @if(! $hasRecords)
                        <tr>
                            <td colspan="7" class="text-center py-4 text-low">
                                No attendance records recorded for {{ \Carbon\Carbon::parse($month.'-01')->format('F Y') }}.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</x-admin>
