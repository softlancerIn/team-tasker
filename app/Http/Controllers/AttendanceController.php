<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today();
        $totalUsers = User::count();
        $presentToday = Attendance::where('date', $today)->whereIn('status', ['Present', 'Late', 'Half-Day'])->count();
        $lateToday = Attendance::where('date', $today)->where('status', 'Late')->count();
        $onLeaveToday = Attendance::where('date', $today)->where('status', 'Leave')->count();
        $absentToday = max(0, $totalUsers - $presentToday - $onLeaveToday);

        $myAttendance = Attendance::where('user_id', Auth::id())->where('date', $today)->first();

        // 7-Day Trend Graph Data (Team)
        $chartLabels = [];
        $chartPresent = [];
        $chartAbsent = [];
        $chartLate = [];
        $chartLeave = [];
        
        // Personal 7-Day Trend Graph Data
        $personalWorkHours = [];

        for ($i = 6; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i);
            $dateStr = $d->format('Y-m-d');
            $chartLabels[] = $d->format('D');
            
            // Team
            $p = Attendance::where('date', $dateStr)->whereIn('status', ['Present', 'Late', 'Half-Day'])->count();
            $l = Attendance::where('date', $dateStr)->where('status', 'Late')->count();
            $lv = Attendance::where('date', $dateStr)->where('status', 'Leave')->count();
            $ab = max(0, $totalUsers - $p - $lv);
            
            $chartPresent[] = $p;
            $chartLate[] = $l;
            $chartLeave[] = $lv;
            $chartAbsent[] = $ab;

            // Personal
            $myAtt = Attendance::where('user_id', Auth::id())->where('date', $dateStr)->first();
            $personalWorkHours[] = $myAtt ? (float)$myAtt->work_hours : 0;
        }

        return view('admin.attendance.dashboard', compact('presentToday', 'lateToday', 'onLeaveToday', 'absentToday', 'myAttendance', 'today', 'chartLabels', 'chartPresent', 'chartAbsent', 'chartLate', 'chartLeave', 'personalWorkHours'));
    }

    public function daily(Request $request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $search = $request->input('search');
        $status = $request->input('status');
        $clockIn = $request->input('clock_in');
        $clockOut = $request->input('clock_out');
        $workHours = $request->input('work_hours');
        
        $users = User::when($search, function ($query) use ($search) {
                return $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($query) use ($status, $date) {
                if ($status == 'Absent') {
                    return $query->whereDoesntHave('attendances', function($q) use ($date) {
                        $q->where('date', $date)->where('status', '!=', 'Absent');
                    });
                } else {
                    return $query->whereHas('attendances', function($q) use ($date, $status) {
                        $q->where('date', $date)->where('status', $status);
                    });
                }
            })
            ->when($clockIn, function ($query) use ($clockIn, $date) {
                return $query->whereHas('attendances', function($q) use ($date, $clockIn) {
                    $q->where('date', $date)->whereTime('clock_in', '>=', $clockIn);
                });
            })
            ->when($clockOut, function ($query) use ($clockOut, $date) {
                return $query->whereHas('attendances', function($q) use ($date, $clockOut) {
                    $q->where('date', $date)->whereTime('clock_out', '<=', $clockOut);
                });
            })
            ->when($workHours, function ($query) use ($workHours, $date) {
                return $query->whereHas('attendances', function($q) use ($date, $workHours) {
                    $q->where('date', $date)->where('work_hours', '>=', (float)$workHours);
                });
            })
            ->paginate(15)
            ->withQueryString();
        
        $attendances = Attendance::with('user')
            ->where('date', $date)
            ->whereIn('user_id', $users->pluck('id'))
            ->get()
            ->keyBy('user_id');

        return view('admin.attendance.daily', compact('users', 'attendances', 'date'));
    }

    public function updateDailyAttendance(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'status' => 'required|in:Present,Late,Half-Day,Absent,Leave,Holiday',
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string'
        ]);

        if ($request->status == 'Absent') {
            // Delete record if it exists and marked as completely absent, or update status
            $attendance = Attendance::where('user_id', $request->user_id)->where('date', $request->date)->first();
            if ($attendance) {
                $attendance->delete();
            }
        } else {
            $workHours = 0;
            if ($request->clock_in && $request->clock_out) {
                $clockIn = Carbon::parse($request->clock_in);
                $clockOut = Carbon::parse($request->clock_out);
                if ($clockOut->greaterThan($clockIn)) {
                    $workHours = round(abs($clockIn->diffInMinutes($clockOut, false)) / 60, 2);
                }
            }
            
            Attendance::updateOrCreate(
                [
                    'user_id' => $request->user_id,
                    'date' => $request->date
                ],
                [
                    'status' => $request->status,
                    'clock_in' => $request->clock_in,
                    'clock_out' => $request->clock_out,
                    'work_hours' => $workHours,
                    'notes' => $request->notes
                ]
            );
        }

        return redirect()->back()->with('success', 'Attendance record updated successfully.');
    }

    public function monthly(Request $request)
    {
        $month = $request->input('month', Carbon::today()->format('Y-m'));
        $search = $request->input('search');
        $minPresent = $request->input('total_present');
        $minLate = $request->input('total_late');
        $minLeave = $request->input('total_leave');
        $minHours = $request->input('total_hours');
        $minAbsent = $request->input('total_absent');
        
        $startDate = Carbon::parse($month . '-01');
        $endDate = $startDate->copy()->endOfMonth();
        
        $workingDaysSetting = Setting::where('key', 'working_days')->value('value') ?? '5';
        $totalWorkingDays = 0;
        
        $currentDate = $startDate->copy();
        $today = Carbon::today();
        
        while ($currentDate->lte($endDate) && $currentDate->lte($today)) {
            if ($workingDaysSetting == '5') {
                if (!$currentDate->isWeekend()) {
                    $totalWorkingDays++;
                }
            } else {
                if (!$currentDate->isSunday()) {
                    $totalWorkingDays++;
                }
            }
            $currentDate->addDay();
        }
        
        $users = User::when($search, function ($query) use ($search) {
                return $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($minPresent, function ($q) use ($minPresent, $startDate, $endDate) {
                return $q->whereHas('attendances', function ($sub) use ($startDate, $endDate) {
                    $sub->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                        ->whereIn('status', ['Present', 'Late', 'Half-Day']);
                }, '>=', (int)$minPresent);
            })
            ->when($minLate, function ($q) use ($minLate, $startDate, $endDate) {
                return $q->whereHas('attendances', function ($sub) use ($startDate, $endDate) {
                    $sub->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                        ->where('status', 'Late');
                }, '>=', (int)$minLate);
            })
            ->when($minLeave, function ($q) use ($minLeave, $startDate, $endDate) {
                return $q->whereHas('attendances', function ($sub) use ($startDate, $endDate) {
                    $sub->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                        ->where('status', 'Leave');
                }, '>=', (int)$minLeave);
            })
            ->when($minAbsent, function ($q) use ($minAbsent, $totalWorkingDays, $startDate, $endDate) {
                $maxPresentAndLeave = max(0, $totalWorkingDays - (int)$minAbsent);
                return $q->whereHas('attendances', function ($sub) use ($startDate, $endDate) {
                    $sub->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                        ->whereIn('status', ['Present', 'Late', 'Half-Day', 'Leave']);
                }, '<=', $maxPresentAndLeave);
            })
            ->when($minHours, function ($q) use ($minHours, $startDate, $endDate) {
                return $q->whereRaw("(SELECT COALESCE(SUM(work_hours), 0) FROM attendances WHERE attendances.user_id = users.id AND date BETWEEN ? AND ?) >= ?", [$startDate->format('Y-m-d'), $endDate->format('Y-m-d'), (float)$minHours]);
            })
            ->paginate(15)
            ->withQueryString();
        
        $attendances = Attendance::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                        ->whereIn('user_id', $users->pluck('id'))
                        ->get()
                        ->groupBy('user_id');
                        
        return view('admin.attendance.monthly', compact('users', 'attendances', 'month', 'startDate', 'endDate', 'totalWorkingDays'));
    }

    public function calendar(Request $request)
    {
        $userId = Auth::id();
        if (Auth::user()->hasPermission('attendance.calendar_all')) {
            $userId = $request->input('user_id', Auth::id());
        }
        
        $attendances = Attendance::where('user_id', $userId)->get();
        $leaves = AttendanceRequest::where('user_id', $userId)->get();

        $events = [];

        foreach ($attendances as $att) {
            $color = match($att->status) {
                'Present' => '#10b981', // success
                'Late' => '#f59e0b', // warning
                'Half-Day' => '#0ea5e9', // info
                'Absent' => '#ef4444', // danger
                default => '#6b7280', // secondary
            };

            $events[] = [
                'title' => $att->status . ($att->clock_in ? ' (' . \Carbon\Carbon::parse($att->clock_in)->format('H:i') . ')' : ''),
                'start' => $att->date,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'allDay' => true,
            ];
        }

        foreach ($leaves as $leave) {
            $leaveColor = match($leave->status) {
                'Approved' => '#10b981', // success
                'Pending' => '#f59e0b', // warning
                'Rejected' => '#ef4444', // danger
                default => '#6b7280', // secondary
            };
            
            $events[] = [
                'title' => 'Leave (' . $leave->status . '): ' . $leave->type . ' - ' . \Illuminate\Support\Str::limit($leave->reason, 20),
                'start' => $leave->start_date,
                'end' => $leave->end_date ? \Carbon\Carbon::parse($leave->end_date)->addDay()->format('Y-m-d') : \Carbon\Carbon::parse($leave->start_date)->addDay()->format('Y-m-d'),
                'backgroundColor' => $leaveColor,
                'borderColor' => $leaveColor,
                'allDay' => true,
                'extendedProps' => [
                    'reason' => $leave->reason,
                    'action_notes' => $leave->action_notes
                ]
            ];
        }

        $users = User::all();

        return view('admin.attendance.calendar', compact('events', 'users', 'userId'));
    }

    public function requests(Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('search');
        
        $requests = AttendanceRequest::with(['user', 'actionBy'])
            ->when(!Auth::user()->hasPermission('attendance.requests_manage'), function ($query) {
                return $query->where('user_id', Auth::id());
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->when($search, function ($query) use ($search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();
            
        return view('admin.attendance.requests', compact('requests'));
    }

    public function storeRequest(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:Leave,Regularization,Overtime',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'reason' => 'required|string',
        ]);

        AttendanceRequest::create([
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'status' => 'Pending',
        ]);

        return redirect()->back()->with('success', 'Request submitted successfully.');
    }

    public function updateRequest(Request $request, $id)
    {
        $attendanceReq = AttendanceRequest::findOrFail($id);
        
        if ($attendanceReq->user_id !== Auth::id() && !Auth::user()->hasPermission('attendance.requests_manage')) {
            return redirect()->back()->with('error', 'Unauthorized to edit this request.');
        }

        if ($attendanceReq->status !== 'Pending') {
            return redirect()->back()->with('error', 'Cannot edit a request that has already been processed.');
        }
        
        $validated = $request->validate([
            'type' => 'required|in:Leave,Regularization,Overtime',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'reason' => 'required|string',
        ]);

        $attendanceReq->update($validated);

        return redirect()->back()->with('success', 'Leave request updated successfully.');
    }

    public function updateRequestStatus(Request $request, $id)
    {
        $attendanceReq = AttendanceRequest::findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|in:Approved,Rejected',
            'action_notes' => 'nullable|string'
        ]);

        $attendanceReq->update([
            'status' => $validated['status'],
            'action_by' => Auth::id(),
            'action_notes' => $validated['action_notes'] ?? null
        ]);

        return redirect()->back()->with('success', 'Request status updated successfully.');
    }

    public function reports(Request $request)
    {
        $users = User::all();
        
        if ($request->has('export')) {
            $month = $request->input('month', Carbon::today()->format('Y-m'));
            $startDate = Carbon::parse($month . '-01')->format('Y-m-d');
            $endDate = Carbon::parse($month . '-01')->endOfMonth()->format('Y-m-d');
            $exportType = $request->input('export');

            if ($exportType === 'single') {
                $userId = $request->input('user_id');
                $query = Attendance::with('user')->whereBetween('date', [$startDate, $endDate]);
                
                if ($userId && $userId != 'all') {
                    $query->where('user_id', $userId);
                }

                $attendances = $query->orderBy('date', 'asc')->get();

                $csvData = "Employee,Date,Status,Clock In,Clock Out,Work Hours\n";
                
                foreach ($attendances as $att) {
                    $csvData .= sprintf(
                        "%s,%s,%s,%s,%s,%s\n",
                        '"' . ($att->user->name ?? 'Unknown') . '"',
                        \Carbon\Carbon::parse($att->date)->format('d/m/Y'),
                        $att->status,
                        $att->clock_in ?? '-',
                        $att->clock_out ?? '-',
                        $att->work_hours ?? '0'
                    );
                }

                return response($csvData)
                    ->header('Content-Type', 'text/csv')
                    ->header('Content-Disposition', 'attachment; filename="attendance_report_'.$month.'.csv"');
            } elseif ($exportType === 'all') {
                $attendances = Attendance::whereBetween('date', [$startDate, $endDate])
                            ->get()
                            ->groupBy('user_id');
                            
                $workingDaysSetting = Setting::where('key', 'working_days')->value('value') ?? '5';
                $totalWorkingDays = 0;
                $currentDate = Carbon::parse($startDate);
                $end = Carbon::parse($endDate);
                $today = Carbon::today();
                
                while ($currentDate->lte($end) && $currentDate->lte($today)) {
                    if ($workingDaysSetting == '5') {
                        if (!$currentDate->isWeekend()) { $totalWorkingDays++; }
                    } else {
                        if (!$currentDate->isSunday()) { $totalWorkingDays++; }
                    }
                    $currentDate->addDay();
                }

                $csvData = "Employee,Total Present,Total Late,Total Absent,Total Leave,Total Hours\n";

                foreach ($users as $user) {
                    $userAtts = $attendances->get($user->id, collect());
                    $present = $userAtts->whereIn('status', ['Present', 'Late', 'Half-Day'])->count();
                    $late = $userAtts->where('status', 'Late')->count();
                    $leave = $userAtts->where('status', 'Leave')->count();
                    $absent = max(0, $totalWorkingDays - $present - $leave); 
                    $totalHours = $userAtts->sum('work_hours');

                    $csvData .= sprintf(
                        "%s,%s,%s,%s,%s,%s\n",
                        '"' . $user->name . '"',
                        $present,
                        $late,
                        $absent,
                        $leave,
                        $totalHours
                    );
                }

                return response($csvData)
                    ->header('Content-Type', 'text/csv')
                    ->header('Content-Disposition', 'attachment; filename="monthly_summary_'.$month.'.csv"');
            }
        }

        return view('admin.attendance.reports', compact('users'));
    }

    public function settings()
    {
        $officeStartTime = Setting::where('key', 'office_start_time')->value('value') ?? '09:15';
        $officeEndTime = Setting::where('key', 'office_end_time')->value('value') ?? '18:00';
        $workingDays = Setting::where('key', 'working_days')->value('value') ?? '5';
        $allowedIps = Setting::where('key', 'allowed_ips')->value('value') ?? '';
        
        return view('admin.attendance.settings', compact('officeStartTime', 'officeEndTime', 'workingDays', 'allowedIps'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'office_start_time' => 'required|date_format:H:i',
            'office_end_time' => 'required|date_format:H:i',
            'working_days' => 'required|in:5,6',
            'allowed_ips' => 'nullable|string',
        ]);
        
        Setting::updateOrCreate(['key' => 'office_start_time'], ['value' => $request->office_start_time]);
        Setting::updateOrCreate(['key' => 'office_end_time'], ['value' => $request->office_end_time]);
        Setting::updateOrCreate(['key' => 'working_days'], ['value' => $request->working_days]);
        Setting::updateOrCreate(['key' => 'allowed_ips'], ['value' => $request->allowed_ips]);
        
        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    public function clockIn(Request $request)
    {
        $today = Carbon::today();
        $now = Carbon::now();
        
        $attendance = Attendance::where('user_id', Auth::id())->where('date', $today)->first();
        
        if ($attendance) {
            return redirect()->back()->with('error', 'You are already clocked in for today.');
        }

        $allowedIpsStr = Setting::where('key', 'allowed_ips')->value('value') ?? '';
        $allowedIps = array_filter(array_map('trim', explode(',', $allowedIpsStr)));
        $currentIp = $request->ip();
        
        if (count($allowedIps) > 0 && !in_array($currentIp, $allowedIps)) {
            return redirect()->back()->with('error', 'Punch in is restricted to the office WiFi network only. Your IP: ' . $currentIp);
        }

        $officeStartTimeStr = Setting::where('key', 'office_start_time')->value('value') ?? '09:15';
        $timeParts = explode(':', $officeStartTimeStr);
        $officeStartTime = Carbon::today()->setTime((int)$timeParts[0], (int)$timeParts[1], 0); 
        
        $status = 'Present';
        if ($now->greaterThan($officeStartTime)) {
            $status = 'Late';
        }

        Attendance::create([
            'user_id' => Auth::id(),
            'date' => $today,
            'clock_in' => $now->format('H:i:s'),
            'status' => $status,
            'notes' => $request->input('notes'),
            'ip_address' => $currentIp,
        ]);

        return redirect()->back()->with('success', 'Clocked in successfully at ' . $now->format('h:i A'));
    }

    public function clockOut(Request $request)
    {
        $today = Carbon::today();
        $now = Carbon::now();
        
        $attendance = Attendance::where('user_id', Auth::id())->where('date', $today)->first();
        
        if (!$attendance) {
            return redirect()->back()->with('error', 'You have not clocked in today.');
        }
        
        if ($attendance->clock_out) {
            return redirect()->back()->with('error', 'You are already clocked out for today.');
        }

        $clockInTime = Carbon::parse($attendance->date . ' ' . $attendance->clock_in);
        $workHours = abs($clockInTime->diffInMinutes($now, false)) / 60;

        $attendance->update([
            'clock_out' => $now->format('H:i:s'),
            'work_hours' => round($workHours, 2),
        ]);

        return redirect()->back()->with('success', 'Clocked out successfully at ' . $now->format('h:i A'));
    }
}
