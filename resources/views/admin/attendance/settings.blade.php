<x-admin>
    <x-slot:title>
        Attendance Settings | Team Tasker
    </x-slot:title>

    <div class="top-bar-premium">
        <div>
            <h1 class="h3 fw-semibold mb-1 text-high">Settings & Reports</h1>
            <p class="text-low mb-0" style="font-size: 0.9rem;">Configure office hours, grace periods, tracking policies, and generate exports.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <!-- Odoo-style View Switcher -->
            <div class="btn-group p-1 rounded-pill" style="background: rgba(255,255,255,0.06); border: 1px solid var(--border-main);">
                <a href="{{ route('admin.attendance.settings') }}" class="btn btn-sm rounded-pill px-3 {{ request()->routeIs('admin.attendance.settings') ? 'btn-premium-primary text-white font-weight-bold shadow-sm' : 'text-low' }}" style="font-size: 0.8rem;">
                    <i class="fas fa-sliders-h me-1"></i> Attendance Settings
                </a>
                @if(Auth::user()->hasRole('super-admin') || Auth::user()->hasPermission('attendance.reports') || Auth::user()->hasPermission('attendance.report'))
                <a href="{{ route('admin.attendance.reports') }}" class="btn btn-sm rounded-pill px-3 {{ request()->routeIs('admin.attendance.reports') ? 'btn-premium-primary text-white font-weight-bold shadow-sm' : 'text-low' }}" style="font-size: 0.8rem;">
                    <i class="fas fa-file-export me-1"></i> Reports & Exports
                </a>
                @endif
            </div>

            @if(Auth::user()->hasRole('super-admin') || Auth::user()->hasPermission('attendance.settings') || Auth::user()->hasPermission('attendance.manage'))
            <button type="submit" form="attendanceSettingsForm" class="btn-premium btn-premium-primary px-4 py-2 shadow-sm d-flex align-items-center gap-2">
                <i class="fas fa-save"></i> Save Settings
            </button>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success py-2 px-3 mb-4 d-flex align-items-center border-0" style="background: rgba(var(--success-rgb), 0.1); color: var(--success);">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger py-2 px-3 mb-4 border-0" style="background: rgba(220, 38, 38, 0.1); color: #dc2626;">
            <div class="d-flex align-items-center mb-2">
                <i class="fas fa-exclamation-triangle me-2"></i> <strong>Please check the form for errors:</strong>
            </div>
            <ul class="mb-0 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="glass-card p-4">
        <form id="attendanceSettingsForm" action="{{ route('admin.attendance.settings.update') }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-4 mb-4">
                    <label class="form-label text-high fw-semibold">Office Start Time (with Grace Period)</label>
                    <input type="time" name="office_start_time" class="form-premium-control w-100" value="{{ $officeStartTime }}" required>
                    <small class="text-low d-block mt-2">Anyone clocking in after this time will be marked as "Late".</small>
                </div>
                
                <div class="col-md-4 mb-4">
                    <label class="form-label text-high fw-semibold">Office End Time</label>
                    <input type="time" name="office_end_time" class="form-premium-control w-100" value="{{ $officeEndTime }}" required>
                    <small class="text-low d-block mt-2">Standard end of shift time.</small>
                </div>
                
                <div class="col-md-4 mb-4">
                    <label class="form-label text-high fw-semibold">Working Days / Week</label>
                    <select name="working_days" class="form-premium-control w-100 bg-dark text-white" required>
                        <option value="5" {{ isset($workingDays) && $workingDays == '5' ? 'selected' : '' }}>5 Days (Mon-Fri)</option>
                        <option value="6" {{ isset($workingDays) && $workingDays == '6' ? 'selected' : '' }}>6 Days (Mon-Sat)</option>
                    </select>
                    <small class="text-low d-block mt-2">Determines expected attendance schedule.</small>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label text-high fw-semibold">Daily Shift Duration (Hours)</label>
                    <div class="input-group">
                        <input type="number" step="0.25" min="1" max="24" name="shift_hours" class="form-premium-control w-100" value="{{ $shiftHours ?? '8.75' }}" placeholder="e.g. 8.75" required>
                    </div>
                    <small class="text-low d-block mt-2">Expected working hours required per regular working day.</small>
                </div>

                <div class="col-md-6 mb-4">
                    <label class="form-label text-high fw-semibold">Weekly Target Work Hours</label>
                    <div class="input-group">
                        <input type="number" step="0.5" min="1" max="168" name="weekly_work_hours" class="form-premium-control w-100" value="{{ $weeklyWorkHours ?? '40' }}" placeholder="e.g. 40 or 44" required>
                    </div>
                    <small class="text-low d-block mt-2">Cumulative target work hours required per employee per week.</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-4">
                    <label class="form-label text-high fw-semibold">Allowed IP Addresses (Office WiFi)</label>
                    <input type="text" name="allowed_ips" class="form-premium-control w-100" value="{{ $allowedIps ?? '' }}" placeholder="e.g. 192.168.1.1, 203.0.113.50">
                    <small class="text-low d-block mt-2">Comma-separated list of IP addresses allowed for punch-in. Leave blank to allow all IPs. Your current IP is: <strong>{{ request()->ip() }}</strong></small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="p-3 rounded border border-subtle" style="background: rgba(var(--primary-rgb), 0.04);">
                        <div class="form-check form-switch d-flex align-items-center gap-3 ps-0 mb-1">
                            <input class="form-check-input ms-0" type="checkbox" role="switch" id="sandwich_leave_enabled" name="sandwich_leave_enabled" value="1" {{ (isset($sandwichLeaveEnabled) && $sandwichLeaveEnabled == '1') ? 'checked' : '' }} style="width: 2.5em; height: 1.25em; cursor: pointer;">
                            <label class="form-check-label fw-bold text-high cursor-pointer" for="sandwich_leave_enabled">
                                <i class="fas fa-layer-group text-warning me-1"></i> Enable Sandwich Leave Policy
                            </label>
                        </div>
                        <small class="text-low d-block ps-md-4 ms-md-4 mt-1">
                            When enabled, if an employee takes leave or is absent/rejected on both the working day immediately before and immediately after scheduled non-working days (such as weekend / weekly off or company holidays, e.g. Friday and Monday), the intervening weekly off or holiday days (e.g. Saturday & Sunday) will also be treated as <strong>unpaid leave</strong> and deducted from salary.
                        </small>
                    </div>
                </div>
            </div>

        </form>
    </div>
</x-admin>
