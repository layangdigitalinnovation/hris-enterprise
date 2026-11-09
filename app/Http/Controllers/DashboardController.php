<?php

namespace App\Http\Controllers;

use App\Models\AttendanceDailySummary;
use App\Models\AttendanceLog;
use App\Models\AttendanceVisit;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Device;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\Payslip;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->check() && auth()->user()->isInvestor()) {
            return redirect()->route('investors.portal');
        }

        $today = Carbon::today()->format('Y-m-d');

        // Statistics
        $totalEmployees = Employee::where('status', 'active')->count();
        $totalBranches = Branch::where('is_active', true)->count();
        $totalDepartments = Department::where('is_active', true)->count();

        // Today's attendance breakdown
        $todaySummaries = AttendanceDailySummary::where('date', $today)->get();
        $presentToday = $todaySummaries->whereIn('status', ['present', 'early_leave'])->count();
        $lateToday = $todaySummaries->where('status', 'late')->count();
        $leaveToday = $todaySummaries->where('status', 'on_leave')->count();
        $absentToday = $todaySummaries->where('status', 'absent')->count();

        // Attendance rate
        $attendanceRate = $totalEmployees > 0 ? round((($presentToday + $lateToday) / $totalEmployees) * 100, 1) : 0;

        // Solution devices status
        $devices = Device::with('branch')->get();
        $onlineDevices = $devices->where('status', 'online')->count();

        // Recent raw logs (stream)
        $recentLogs = AttendanceLog::with(['employee.department', 'employee.branch', 'device.branch'])
            ->orderBy('log_time', 'desc')
            ->limit(8)
            ->get();

        // Pending leaves requiring approval
        $pendingLeaves = LeaveRequest::where('status', 'submitted')
            ->with(['employee.department', 'leaveType'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Weekly attendance trends (last 7 days)
        $trendDates = collect();
        $trendData = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i)->format('Y-m-d');
            $trendDates->push(Carbon::parse($d)->translatedFormat('d M'));
            $sums = AttendanceDailySummary::where('date', $d)->get();
            $trendData['present'][] = $sums->whereIn('status', ['present', 'early_leave'])->count();
            $trendData['late'][] = $sums->where('status', 'late')->count();
            $trendData['absent'][] = $sums->where('status', 'absent')->count();
        }

        // Employee Self-Service Portal Data
        $user = auth()->user();
        $myEmployee = $user->employee?->load(['department', 'position', 'branch']);
        $myTodayAttendance = null;
        $myLeaveBalances = collect();
        $myRecentLogs = collect();
        $myPendingLeaves = collect();
        $myLatestPayslip = null;
        $allBranches = Branch::where('is_active', true)->get();

        if ($myEmployee) {
            $myTodayAttendance = AttendanceDailySummary::where('employee_id', $myEmployee->id)->where('date', $today)->first();
            $myLeaveBalances = LeaveBalance::where('employee_id', $myEmployee->id)->where('year', date('Y'))->with('leaveType')->get();
            $myRecentLogs = AttendanceLog::where('employee_id', $myEmployee->id)
                ->with(['device.branch', 'employee.branch'])
                ->orderBy('log_time', 'desc')
                ->limit(6)
                ->get();
            $myPendingLeaves = LeaveRequest::where('employee_id', $myEmployee->id)->orderBy('created_at', 'desc')->limit(5)->get();
            $myLatestPayslip = \App\Models\Payslip::whereHas('payrollItem', fn($q) => $q->where('employee_id', $myEmployee->id))->latest('generated_at')->first();
            $myActiveVisit = \App\Models\AttendanceVisit::where('employee_id', $myEmployee->id)->where('status', 'ongoing')->latest()->first();
            $myTodayVisitsCount = \App\Models\AttendanceVisit::where('employee_id', $myEmployee->id)->whereDate('check_in_at', $today)->count();
        } else {
            $myActiveVisit = null;
            $myTodayVisitsCount = 0;
        }

        // Live Real-Time GPS Tracking Map Payload (Daily Attendance & Field Visits)
        $gpsMapData = $this->getGpsMapDataPayload($today);

        return view('dashboard.index', compact(
            'totalEmployees',
            'totalBranches',
            'totalDepartments',
            'presentToday',
            'lateToday',
            'leaveToday',
            'absentToday',
            'attendanceRate',
            'devices',
            'onlineDevices',
            'recentLogs',
            'pendingLeaves',
            'trendDates',
            'trendData',
            'myEmployee',
            'myTodayAttendance',
            'myLeaveBalances',
            'myRecentLogs',
            'myPendingLeaves',
            'myLatestPayslip',
            'allBranches',
            'myActiveVisit',
            'myTodayVisitsCount',
            'gpsMapData'
        ));
    }

    /**
     * AJAX endpoint for real-time GPS tracking data by selected date.
     */
    public function gpsMapData(Request $request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = Carbon::today()->format('Y-m-d');
        }

        $payload = $this->getGpsMapDataPayload($date);
        return response()->json($payload);
    }

    /**
     * Compile real-time daily GPS tracking payload for all employees and branch geofences.
     */
    public function getGpsMapDataPayload(string $date): array
    {
        $parsedDate = Carbon::parse($date);

        // 1. Branches & Geofence Zones
        $branches = Branch::where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'name' => $b->name,
                    'code' => $b->code,
                    'latitude' => (float) $b->latitude,
                    'longitude' => (float) $b->longitude,
                    'radius_meters' => (int) ($b->radius_meters ?: 100),
                    'timezone' => $b->timezone,
                    'country_name' => $b->country_name,
                    'country_flag' => $b->country_flag,
                    'address' => $b->address,
                ];
            });

        // 2. All Active Employees (for roster)
        $allActiveEmployees = Employee::where('status', 'active')
            ->with(['department', 'branch', 'position'])
            ->orderBy('first_name', 'asc')
            ->get();

        $markers = [];
        $employeeLatestMap = [];
        $routes = [];

        // 3. Attendance Visits on selected date
        $visits = AttendanceVisit::with(['employee.department', 'employee.branch', 'employee.position', 'branch'])
            ->where(function ($q) use ($date) {
                $q->whereDate('check_in_at', $date)
                  ->orWhere(function ($sub) {
                      $sub->where('status', 'ongoing')->whereNull('check_out_at');
                  });
            })
            ->orderBy('check_in_at', 'asc')
            ->get();

        foreach ($visits as $v) {
            $emp = $v->employee;
            if (!$emp) continue;

            $initials = strtoupper(substr($emp->first_name ?? 'K', 0, 1) . substr($emp->last_name ?? '', 0, 1));
            $empInfo = [
                'id' => $emp->id,
                'name' => $emp->full_name,
                'code' => $emp->employee_code,
                'department' => $emp->department?->name ?? 'Operasional',
                'position' => $emp->position?->title ?? 'Staff',
                'initials' => $initials ?: 'EM',
                'branch_name' => $emp->branch?->name ?? '-',
                'branch_flag' => $emp->branch?->country_flag ?? '🇮🇩',
            ];

            // Check-In Point
            $inTime = Carbon::parse($v->check_in_at);
            $inMarker = [
                'id' => "visit-{$v->id}-in",
                'category' => 'visit',
                'type' => 'visit_in',
                'sub_type' => 'check_in',
                'status' => $v->status,
                'visit_id' => $v->id,
                'visit_number' => $v->visit_number,
                'title' => $v->client_name,
                'client_name' => $v->client_name,
                'purpose' => $v->purpose,
                'address' => $v->check_in_address ?: 'Titik Lokasi Kunjungan Klien',
                'latitude' => (float) $v->check_in_lat,
                'longitude' => (float) $v->check_in_lon,
                'time' => $inTime->format('H:i:s'),
                'datetime' => $inTime->format('Y-m-d H:i:s'),
                'timezone' => $v->timezone ?: 'Asia/Jakarta',
                'employee' => $empInfo,
                'photo_url' => $v->photo_path ? asset('storage/' . $v->photo_path) : null,
            ];
            $markers[] = $inMarker;
            $employeeLatestMap[$emp->id] = $inMarker;

            // Check-Out Point (if completed)
            if ($v->check_out_lat && $v->check_out_lon && $v->check_out_at) {
                $outTime = Carbon::parse($v->check_out_at);
                $outMarker = [
                    'id' => "visit-{$v->id}-out",
                    'category' => 'visit',
                    'type' => 'visit_out',
                    'sub_type' => 'check_out',
                    'status' => $v->status,
                    'visit_id' => $v->id,
                    'visit_number' => $v->visit_number,
                    'title' => $v->client_name,
                    'client_name' => $v->client_name,
                    'purpose' => $v->purpose,
                    'outcome' => $v->outcome,
                    'duration_minutes' => $v->duration_minutes,
                    'address' => $v->check_out_address ?: 'Titik Lokasi Selesai Kunjungan',
                    'latitude' => (float) $v->check_out_lat,
                    'longitude' => (float) $v->check_out_lon,
                    'time' => $outTime->format('H:i:s'),
                    'datetime' => $outTime->format('Y-m-d H:i:s'),
                    'timezone' => $v->timezone ?: 'Asia/Jakarta',
                    'employee' => $empInfo,
                    'photo_url' => $v->photo_path ? asset('storage/' . $v->photo_path) : null,
                ];
                $markers[] = $outMarker;
                $employeeLatestMap[$emp->id] = $outMarker;

                $routes[] = [
                    'visit_id' => $v->id,
                    'visit_number' => $v->visit_number,
                    'employee_name' => $emp->full_name,
                    'client_name' => $v->client_name,
                    'from' => [(float) $v->check_in_lat, (float) $v->check_in_lon],
                    'to' => [(float) $v->check_out_lat, (float) $v->check_out_lon],
                    'color' => '#10b981',
                ];
            }
        }

        // 4. Mobile GPS Geofence Attendance Logs
        $gpsLogs = AttendanceLog::with(['employee.department', 'employee.branch', 'employee.position'])
            ->where('verify_type', 'gps')
            ->whereDate('log_time', $date)
            ->orderBy('log_time', 'asc')
            ->get();

        foreach ($gpsLogs as $l) {
            $payload = $l->raw_payload ?? [];
            if (empty($payload['latitude']) || empty($payload['longitude'])) continue;

            $emp = $l->employee;
            if ($emp) {
                $initials = strtoupper(substr($emp->first_name ?? 'K', 0, 1) . substr($emp->last_name ?? '', 0, 1));
                $empInfo = [
                    'id' => $emp->id,
                    'name' => $emp->full_name,
                    'code' => $emp->employee_code,
                    'department' => $emp->department?->name ?? 'Operasional',
                    'position' => $emp->position?->title ?? 'Staff',
                    'initials' => $initials ?: 'EM',
                    'branch_name' => $emp->branch?->name ?? '-',
                    'branch_flag' => $emp->branch?->country_flag ?? '🇮🇩',
                ];
            } else {
                $empInfo = [
                    'id' => null,
                    'name' => 'PIN: ' . $l->device_pin,
                    'code' => $l->device_pin,
                    'department' => 'Staff',
                    'position' => 'Staff',
                    'initials' => 'PIN',
                    'branch_name' => '-',
                    'branch_flag' => '📍',
                ];
            }

            $logTime = Carbon::parse($l->log_time);
            $tz = $payload['branch_timezone'] ?? ($l->timezone ?: 'Asia/Jakarta');
            $branchName = $payload['branch_name'] ?? ($emp?->branch?->name ?? 'Kantor Cabang');
            $dist = (float) ($payload['distance_meters'] ?? 0);
            $radiusLimit = (int) ($payload['radius_limit_meters'] ?? 100);

            $gpsMarker = [
                'id' => "gps-{$l->id}",
                'category' => 'gps',
                'type' => 'gps_' . ($l->log_type ?: 'check_in'),
                'sub_type' => $l->log_type ?: 'check_in',
                'status' => 'recorded',
                'title' => __('Presensi Mandiri GPS Geofence'),
                'branch_name' => $branchName,
                'distance_meters' => $dist,
                'radius_limit_meters' => $radiusLimit,
                'country_flag' => $payload['country_flag'] ?? '🇮🇩',
                'address' => __('Jarak :dist m dari :branch (radius max :rad m)', [
                    'dist' => $dist,
                    'branch' => $branchName,
                    'rad' => $radiusLimit
                ]),
                'latitude' => (float) $payload['latitude'],
                'longitude' => (float) $payload['longitude'],
                'time' => $logTime->format('H:i:s'),
                'datetime' => $logTime->format('Y-m-d H:i:s'),
                'timezone' => $tz,
                'employee' => $empInfo,
            ];

            $markers[] = $gpsMarker;
            if ($emp) {
                $employeeLatestMap[$emp->id] = $gpsMarker;
            }
        }

        // 5. Build Employee Roster (All Active Employees with today's status)
        $roster = $allActiveEmployees->map(function ($emp) use ($employeeLatestMap) {
            $hasActivity = isset($employeeLatestMap[$emp->id]);
            $latest = $hasActivity ? $employeeLatestMap[$emp->id] : null;
            $initials = strtoupper(substr($emp->first_name ?? 'K', 0, 1) . substr($emp->last_name ?? '', 0, 1));

            return [
                'id' => $emp->id,
                'name' => $emp->full_name,
                'code' => $emp->employee_code,
                'department' => $emp->department?->name ?? '-',
                'position' => $emp->position?->title ?? 'Staff',
                'branch_name' => $emp->branch?->name ?? '-',
                'branch_flag' => $emp->branch?->country_flag ?? '🇮🇩',
                'initials' => $initials ?: 'EM',
                'has_activity' => $hasActivity,
                'latest_marker_id' => $latest['id'] ?? null,
                'latitude' => $latest['latitude'] ?? null,
                'longitude' => $latest['longitude'] ?? null,
                'category' => $latest['category'] ?? null,
                'sub_type' => $latest['sub_type'] ?? null,
                'time' => $latest['time'] ?? null,
                'activity_title' => $latest ? ($latest['category'] === 'visit' ? $latest['client_name'] : $latest['branch_name']) : null,
                'status' => $latest['status'] ?? null,
            ];
        })->sortByDesc('has_activity')->values();

        // 6. Statistics
        $trackedEmpCount = count($employeeLatestMap);
        $totalVisitsCount = $visits->count();
        $totalGpsLogsCount = $gpsLogs->count();
        $ongoingVisitsCount = $visits->where('status', 'ongoing')->count();

        return [
            'date' => $date,
            'date_formatted' => $parsedDate->locale(app()->getLocale())->translatedFormat('l, d F Y'),
            'branches' => $branches,
            'markers' => $markers,
            'routes' => $routes,
            'roster' => $roster,
            'stats' => [
                'total_tracked_employees' => $trackedEmpCount,
                'total_active_employees' => $allActiveEmployees->count(),
                'total_visits' => $totalVisitsCount,
                'total_gps_punches' => $totalGpsLogsCount,
                'ongoing_visits' => $ongoingVisitsCount,
                'total_markers' => count($markers),
                'total_branches' => $branches->count(),
            ],
        ];
    }
}
