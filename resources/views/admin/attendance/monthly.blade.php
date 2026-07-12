<x-admin>
    <x-slot:title>
        Monthly Attendance | Team Tasker
    </x-slot:title>

    <div class="top-bar-premium">
        <div>
            <h1 class="h3 fw-semibold mb-1 text-high">Monthly Attendance</h1>
            <p class="text-low mb-0" style="font-size: 0.9rem;">View and manage monthly attendance aggregated data.</p>
        </div>
    </div>

    <div class="data-grid-wrapper mb-5">
        <div class="data-grid-top">
            <div class="data-grid-search">
                <form action="{{ route('admin.attendance.monthly') }}" method="GET" class="d-flex align-items-center m-0 w-100">
                    <i class="fas fa-search me-2 text-low"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search employees..." class="border-1 bg-transparent text-high w-100" style="outline: none;">
                    @if(request('month'))
                        <input type="hidden" name="month" value="{{ request('month') }}">
                    @endif
                    @if(request('total_present'))
                        <input type="hidden" name="total_present" value="{{ request('total_present') }}">
                    @endif
                    @if(request('total_late'))
                        <input type="hidden" name="total_late" value="{{ request('total_late') }}">
                    @endif
                    @if(request('total_absent'))
                        <input type="hidden" name="total_absent" value="{{ request('total_absent') }}">
                    @endif
                    @if(request('total_leave'))
                        <input type="hidden" name="total_leave" value="{{ request('total_leave') }}">
                    @endif
                    @if(request('total_hours'))
                        <input type="hidden" name="total_hours" value="{{ request('total_hours') }}">
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
                        <th>Total Present</th>
                        <th>Total Late</th>
                        <th>Total Absent</th>
                        <th>Total Leave</th>
                        <th>Total Hours</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        @php
                            $userAttendances = $attendances->get($user->id, collect());
                            $present = $userAttendances->whereIn('status', ['Present', 'Late', 'Half-Day'])->count();
                            $late = $userAttendances->where('status', 'Late')->count();
                            $leave = $userAttendances->where('status', 'Leave')->count();
                            
                            // We use the computed $totalWorkingDays based on settings
                            $expectedDays = $totalWorkingDays;
                            $absent = max(0, $expectedDays - $present - $leave); 
                            
                            $totalHours = $userAttendances->sum('work_hours');
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
                                <span class="badge-premium bg-success-subtle text-success border border-success border-opacity-25 px-2 py-1">
                                    {{ $present }}
                                </span>
                            </td>
                            <td>
                                <span class="badge-premium bg-warning-subtle text-warning border border-warning border-opacity-25 px-2 py-1">
                                    {{ $late }}
                                </span>
                            </td>
                            <td>
                                <span class="badge-premium bg-danger-subtle text-danger border border-danger border-opacity-25 px-2 py-1">
                                    {{ $absent }}
                                </span>
                            </td>
                            <td>
                                <span class="badge-premium bg-secondary-subtle text-secondary border border-secondary border-opacity-25 px-2 py-1">
                                    {{ $leave }}
                                </span>
                            </td>
                            <td class="text-low">
                                {{ $totalHours }} hrs
                            </td>
                            <td>
                                <a href="{{ route('admin.attendance.calendar', ['user_id' => $user->id]) }}" class="btn btn-sm btn-premium-secondary" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin>

<div class="filter-slideover" id="filterSlideoverAttendance">
    <form action="{{ route('admin.attendance.monthly') }}" method="GET" class="h-100 d-flex flex-column">
        <div class="filter-slideover-header">
            <h4><i class="fas fa-sliders-h text-low me-2"></i> Advanced Filters</h4>
            <div class="filter-slideover-close" onclick="document.querySelector('.filter-slideover').classList.remove('show')">
                <i class="fas fa-times"></i>
            </div>
        </div>
        <div class="filter-slideover-body">
            <div class="mb-4">
                <label class="heading-label d-block mb-2 text-low">MONTH</label>
                <input type="month" name="month" value="{{ request('month', $month) }}" class="form-premium-control bg-white text-dark border-main">
            </div>
            <div class="mb-4">
                <label class="heading-label d-block mb-2 text-low">TOTAL PRESENT (MIN)</label>
                <input type="number" name="total_present" value="{{ request('total_present') }}" placeholder="e.g. 20" class="form-premium-control bg-white text-dark border-main">
            </div>
            <div class="mb-4">
                <label class="heading-label d-block mb-2 text-low">TOTAL LATE (MIN)</label>
                <input type="number" name="total_late" value="{{ request('total_late') }}" placeholder="e.g. 3" class="form-premium-control bg-white text-dark border-main">
            </div>
            <div class="mb-4">
                <label class="heading-label d-block mb-2 text-low">TOTAL ABSENT (MIN)</label>
                <input type="number" name="total_absent" value="{{ request('total_absent') }}" placeholder="e.g. 2" class="form-premium-control bg-white text-dark border-main">
            </div>
            <div class="mb-4">
                <label class="heading-label d-block mb-2 text-low">TOTAL LEAVE (MIN)</label>
                <input type="number" name="total_leave" value="{{ request('total_leave') }}" placeholder="e.g. 1" class="form-premium-control bg-white text-dark border-main">
            </div>
            <div class="mb-4">
                <label class="heading-label d-block mb-2 text-low">TOTAL HOURS (MIN)</label>
                <input type="number" step="1" name="total_hours" value="{{ request('total_hours') }}" placeholder="e.g. 160" class="form-premium-control bg-white text-dark border-main">
            </div>
        </div>
        <div class="filter-slideover-footer">
            <a href="{{ route('admin.attendance.monthly') }}" class="btn-premium btn-premium-secondary w-50 justify-content-center bg-white text-dark border-main">Reset</a>
            <button type="submit" class="btn-premium btn-premium-primary w-50 justify-content-center" style="background: #0ea5e9;">Apply Filters</button>
        </div>
    </form>
</div>
