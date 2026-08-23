<x-admin>
    <x-slot:title>
        Attendance Dashboard | Team Tasker
    </x-slot:title>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 dark:text-white" style="font-weight: 600;">Attendance Dashboard</h4>
        <div class="d-flex gap-2">
            @if(session('success'))
                <div class="alert alert-success py-2 px-3 mb-0 d-flex align-items-center border-0 me-3" style="background: rgba(var(--success-rgb), 0.1); color: var(--success);">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger py-2 px-3 mb-0 d-flex align-items-center border-0 me-3" style="background: rgba(var(--danger-rgb), 0.1); color: var(--danger);">
                    <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                </div>
            @endif

            @if(!$myAttendance)
                <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#globalClockInModal">
                    <i class="fas fa-sign-in-alt"></i> Clock In
                </button>
            @elseif(!$myAttendance->clock_out)
                <button type="button" class="btn btn-danger d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#globalClockOutModal">
                    <i class="fas fa-sign-out-alt"></i> Clock Out
                </button>
            @else
                <button type="button" class="btn btn-secondary d-flex align-items-center gap-2" disabled title="You have already completed your shift today">
                    <i class="fas fa-check"></i> Shift Completed
                </button>
            @endif
        </div>
    </div>

    @if($myAttendance)
    <div class="glass-card p-4 mb-4" style="border: 1px solid var(--border-main);">
        <div class="row align-items-center">
            <div class="col-md-3 border-end border-secondary border-opacity-25">
                <p class="text-low mb-1" style="font-size: 0.85rem;">Clock In Time</p>
                <h4 class="mb-0 text-high fw-bold">{{ \Carbon\Carbon::parse($myAttendance->clock_in)->format('h:i A') }}</h4>
            </div>
            <div class="col-md-3 border-end border-secondary border-opacity-25 px-4">
                <p class="text-low mb-1" style="font-size: 0.85rem;">Clock Out Time</p>
                <h4 class="mb-0 text-high fw-bold">{{ $myAttendance->clock_out ? \Carbon\Carbon::parse($myAttendance->clock_out)->format('h:i A') : '--:--' }}</h4>
            </div>
            <div class="col-md-3 border-end border-secondary border-opacity-25 px-4">
                <p class="text-low mb-1" style="font-size: 0.85rem;">Work Hours</p>
                <h4 class="mb-0 text-high fw-bold">{{ $myAttendance->work_hours ? $myAttendance->work_hours . ' hrs' : 'Running...' }}</h4>
            </div>
            <div class="col-md-3 px-4">
                <p class="text-low mb-1" style="font-size: 0.85rem;">Punch In Location</p>
                <h6 class="mb-0 text-high fw-bold text-truncate" title="{{ $myAttendance->location ?? 'Not Recorded' }}">
                    <i class="fas fa-map-marker-alt text-primary me-1"></i>{{ $myAttendance->location ?? 'Not Recorded' }}
                </h6>
            </div>
        </div>
    </div>
    @endif

    @if (Auth::user()->hasPermission('attendance.dashboard_overview'))
    <div class="row row-cols-1 row-cols-md-4 g-4 mb-4">
        <div class="col">
            <div class="glass-card p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-low mb-1" style="font-size: 0.85rem;">Present Today</p>
                        <h3 class="mb-0 text-high fw-bold">{{ $presentToday }}</h3>
                    </div>
                    <div class="stat-icon-premium icon-success-premium" style="width: 45px; height: 45px;">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="glass-card p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-low mb-1" style="font-size: 0.85rem;">Absent Today</p>
                        <h3 class="mb-0 text-high fw-bold">{{ $absentToday }}</h3>
                    </div>
                    <div class="stat-icon-premium icon-danger-premium" style="width: 45px; height: 45px;">
                        <i class="fas fa-user-times"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="glass-card p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-low mb-1" style="font-size: 0.85rem;">Late Today</p>
                        <h3 class="mb-0 text-high fw-bold">{{ $lateToday }}</h3>
                    </div>
                    <div class="stat-icon-premium icon-warning-premium" style="width: 45px; height: 45px;">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="glass-card p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-low mb-1" style="font-size: 0.85rem;">On Leave Today</p>
                        <h3 class="mb-0 text-high fw-bold">{{ $onLeaveToday }}</h3>
                    </div>
                    <div class="stat-icon-premium icon-info-premium" style="width: 45px; height: 45px;">
                        <i class="fas fa-calendar-minus"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="glass-card p-4 mb-4">
        <h5 class="text-high fw-semibold mb-3">Attendance Trend (Last 7 Days)</h5>
        <canvas id="attendanceTrendChart" height="80"></canvas>
    </div>
    @endif

    <div class="glass-card p-4 mb-4">
        <h5 class="text-high fw-semibold mb-3">Your Work Hours (Last 7 Days)</h5>
        <canvas id="personalTrendChart" height="100"></canvas>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Render Personal Chart
            if (document.getElementById('personalTrendChart')) {
                const ctx = document.getElementById('personalTrendChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($chartLabels) !!},
                        datasets: [{
                            label: 'Work Hours',
                            data: {!! json_encode($personalWorkHours) !!},
                            borderColor: '#0ea5e9',
                            backgroundColor: 'rgba(14, 165, 233, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { 
                                beginAtZero: true, 
                                ticks: { color: document.documentElement.classList.contains('dark-theme') ? '#9ca3af' : '#6b7280' },
                                grid: { color: document.documentElement.classList.contains('dark-theme') ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)' }
                            },
                            x: { 
                                ticks: { color: document.documentElement.classList.contains('dark-theme') ? '#9ca3af' : '#6b7280' },
                                grid: { display: false }
                            }
                        }
                    }
                });
            }

            // Render Team Chart (Admin Only)
            if (document.getElementById('attendanceTrendChart')) {
                const ctxTeam = document.getElementById('attendanceTrendChart').getContext('2d');
                new Chart(ctxTeam, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($chartLabels) !!},
                        datasets: [
                            {
                                label: 'Present & Half-Day',
                                data: {!! json_encode($chartPresent) !!},
                                backgroundColor: '#10b981',
                                borderRadius: 4
                            },
                            {
                                label: 'Late',
                                data: {!! json_encode($chartLate) !!},
                                backgroundColor: '#f59e0b',
                                borderRadius: 4
                            },
                            {
                                label: 'Leave',
                                data: {!! json_encode($chartLeave) !!},
                                backgroundColor: '#0ea5e9',
                                borderRadius: 4
                            },
                            {
                                label: 'Absent',
                                data: {!! json_encode($chartAbsent) !!},
                                backgroundColor: '#ef4444',
                                borderRadius: 4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'top', labels: { color: document.documentElement.classList.contains('dark-theme') ? '#e5e7eb' : '#4b5563' } }
                        },
                        scales: {
                            y: { 
                                beginAtZero: true, 
                                stacked: true,
                                ticks: { precision: 0, color: document.documentElement.classList.contains('dark-theme') ? '#9ca3af' : '#6b7280' },
                                grid: { color: document.documentElement.classList.contains('dark-theme') ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)' }
                            },
                            x: { 
                                stacked: true,
                                ticks: { color: document.documentElement.classList.contains('dark-theme') ? '#9ca3af' : '#6b7280' },
                                grid: { display: false }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</x-admin>
