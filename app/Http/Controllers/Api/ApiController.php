<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceAdjustment;
use App\Models\AttendanceDailySummary;
use App\Models\AttendanceLog;
use App\Models\AttendanceVisit;
use App\Models\Device;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\PayrollPeriod;
use App\Services\Attendance\AttendanceEngineService;
use App\Services\Device\SolutionConnectorService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    public function __construct(
        protected AttendanceEngineService $engineService,
        protected SolutionConnectorService $connectorService
    ) {}

    /**
     * GET /api/v1/employees
     */
    public function getEmployees(Request $request): JsonResponse
    {
        $query = Employee::with(['department', 'position', 'branch']);

        if ($request->has('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        $employees = $query->paginate($request->query('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $employees,
        ]);
    }

    /**
     * POST /api/v1/employees
     */
    public function storeEmployee(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'employee_code' => 'required|string|max:50|unique:employees,employee_code',
            'email' => 'required|email|max:150|unique:employees,email',
            'branch_id' => 'required|exists:branches,id',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'join_date' => 'required|date',
            'employment_status' => 'required|in:permanent,contract,probation,intern',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $employee = Employee::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Karyawan berhasil dibuat.',
            'data' => $employee,
        ], 201);
    }

    /**
     * GET /api/v1/employees/{id}
     */
    public function getEmployee(int $id): JsonResponse
    {
        $employee = Employee::with(['department', 'position', 'branch', 'manager', 'deviceMappings.device', 'leaveBalances.leaveType'])->find($id);

        if (!$employee) {
            return response()->json(['status' => 'error', 'message' => 'Karyawan tidak ditemukan.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $employee,
        ]);
    }

    /**
     * GET /api/v1/attendance/daily
     */
    public function getDailyAttendance(Request $request): JsonResponse
    {
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));
        $summaries = AttendanceDailySummary::with(['employee.department', 'shift'])
            ->where('date', $date)
            ->get();

        return response()->json([
            'status' => 'success',
            'date' => $date,
            'total' => $summaries->count(),
            'data' => $summaries,
        ]);
    }

    /**
     * GET /api/v1/attendance/logs
     */
    public function getAttendanceLogs(Request $request): JsonResponse
    {
        $query = AttendanceLog::with(['device', 'employee']);

        if ($date = $request->query('date')) {
            $query->whereDate('log_time', $date);
        }

        $logs = $query->orderBy('log_time', 'desc')->paginate($request->query('per_page', 25));

        return response()->json([
            'status' => 'success',
            'data' => $logs,
        ]);
    }

    /**
     * POST /api/v1/attendance/adjustments
     */
    public function storeAdjustment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'requested_clock_in' => 'nullable|date_format:H:i',
            'requested_clock_out' => 'nullable|date_format:H:i',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $adjustment = AttendanceAdjustment::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Pengajuan koreksi presensi berhasil didaftarkan.',
            'data' => $adjustment,
        ], 201);
    }

    /**
     * POST /api/v1/attendance/check-in
     * Presensi GPS Masuk dengan verifikasi geofencing radius dan zona waktu kantor cabang (Indonesia & Arab Saudi).
     */
    public function checkIn(Request $request): JsonResponse
    {
        return $this->processGpsAttendance($request, 'check_in');
    }

    /**
     * POST /api/v1/attendance/check-out
     * Presensi GPS Keluar dengan verifikasi geofencing radius dan zona waktu kantor cabang (Indonesia & Arab Saudi).
     */
    public function checkOut(Request $request): JsonResponse
    {
        return $this->processGpsAttendance($request, 'check_out');
    }

    /**
     * Helper proses presensi GPS
     */
    protected function processGpsAttendance(Request $request, string $logType): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'notes' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $employee = Employee::with('branch')->find($request->input('employee_id'));
        $branch = $employee->branch;

        if (!$branch) {
            return response()->json([
                'status' => 'error',
                'message' => "Karyawan {$employee->full_name} belum terdaftar pada kantor cabang operasional manapun.",
            ], 422);
        }

        if (!$branch->hasCoordinates()) {
            return response()->json([
                'status' => 'error',
                'message' => "Kantor cabang {$branch->name} belum mengatur koordinat GPS geofence.",
            ], 422);
        }

        $lat = (float) $request->input('latitude');
        $lon = (float) $request->input('longitude');
        $check = $branch->isWithinRadius($lat, $lon);

        if (!$check['allowed']) {
            return response()->json([
                'status' => 'error',
                'message' => "Presensi GPS ditolak. Anda berada sejauh {$check['distance']}m dari {$branch->name} (batas maksimum radius {$check['radius']}m).",
                'data' => [
                    'distance_meters' => $check['distance'],
                    'radius_meters' => $check['radius'],
                    'branch' => [
                        'id' => $branch->id,
                        'name' => $branch->name,
                        'code' => $branch->code,
                        'timezone' => $branch->timezone,
                        'country' => $branch->country_name,
                        'latitude' => $branch->latitude,
                        'longitude' => $branch->longitude,
                    ],
                ],
            ], 422);
        }

        // Branch local time (respecting Indonesia WIB/WITA/WIT or Saudi Arabia AST)
        $tz = $branch->timezone ?: 'Asia/Jakarta';
        $localNow = Carbon::now($tz);

        $pin = (string) ($employee->deviceMappings()->first()?->device_pin ?? $employee->employee_code);
        $hashSig = hash('sha256', "gps_{$employee->id}_{$localNow->format('Ymd_His')}_{$logType}");

        $log = AttendanceLog::create([
            'employee_id' => $employee->id,
            'device_pin' => $pin,
            'log_time' => $localNow->format('Y-m-d H:i:s'),
            'verify_type' => 'gps',
            'log_type' => $logType,
            'raw_payload' => [
                'latitude' => $lat,
                'longitude' => $lon,
                'distance_meters' => $check['distance'],
                'radius_limit_meters' => $check['radius'],
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'branch_code' => $branch->code,
                'branch_timezone' => $tz,
                'country' => $branch->country_name,
                'country_flag' => $branch->country_flag,
                'notes' => $request->input('notes'),
                'ip' => $request->ip(),
            ],
            'hash_signature' => $hashSig,
            'status' => 'pending',
        ]);

        // Process daily attendance in engine using the branch local date
        $engineResult = $this->engineService->processDailyAttendance($localNow->format('Y-m-d'), $employee->id);

        $typeName = $logType === 'check_in' ? 'Masuk (Check In)' : 'Keluar (Check Out)';

        return response()->json([
            'status' => 'success',
            'message' => "Presensi GPS {$typeName} berhasil dicatat di {$branch->name} ({$branch->country_flag} {$tz}).",
            'data' => [
                'log_id' => $log->id,
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->full_name,
                    'code' => $employee->employee_code,
                ],
                'branch' => [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'timezone' => $tz,
                    'country' => $branch->country_name,
                    'country_flag' => $branch->country_flag,
                ],
                'log_time' => $localNow->format('Y-m-d H:i:s'),
                'log_type' => $logType,
                'distance_meters' => $check['distance'],
                'radius_meters' => $check['radius'],
                'engine_result' => $engineResult,
            ],
        ], 201);
    }

    /**
     * POST /api/v1/attendance/verify-location
     * Cek apakah koordinat GPS pengguna berada di dalam radius presensi cabang.
     */
    public function verifyLocation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'branch_id' => 'nullable|exists:branches,id',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $branch = null;
        if ($request->filled('branch_id')) {
            $branch = Branch::find($request->input('branch_id'));
        } elseif ($request->filled('employee_id')) {
            $employee = Employee::with('branch')->find($request->input('employee_id'));
            $branch = $employee?->branch;
        }

        if (!$branch) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kantor cabang tidak ditemukan.',
            ], 404);
        }

        $check = $branch->isWithinRadius((float) $request->input('latitude'), (float) $request->input('longitude'));
        $localTime = $branch->getLocalTime();

        return response()->json([
            'status' => 'success',
            'data' => [
                'branch' => [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'code' => $branch->code,
                    'timezone' => $branch->timezone,
                    'timezone_label' => $branch->timezone_label,
                    'country' => $branch->country_name,
                    'country_flag' => $branch->country_flag,
                    'local_time' => $localTime->format('H:i:s'),
                    'latitude' => $branch->latitude,
                    'longitude' => $branch->longitude,
                    'radius_meters' => $branch->radius_meters,
                ],
                'distance_meters' => $check['distance'],
                'is_within_radius' => $check['allowed'],
                'message' => $check['message'],
            ],
        ]);
    }

    /**
     * GET /api/v1/leave/balances
     */
    public function getLeaveBalances(Request $request): JsonResponse
    {
        $year = $request->query('year', Carbon::now()->year);
        $employeeId = $request->query('employee_id');

        $query = LeaveBalance::with(['employee', 'leaveType'])->where('year', $year);
        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        return response()->json([
            'status' => 'success',
            'year' => $year,
            'data' => $query->get(),
        ]);
    }

    /**
     * POST /api/v1/leave/requests
     */
    public function storeLeaveRequest(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $start = Carbon::parse($request->input('start_date'));
        $end = Carbon::parse($request->input('end_date'));
        $workdays = 0;
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if (!$date->isWeekend()) {
                $workdays++;
            }
        }

        $count = LeaveRequest::count() + 1;
        $reqNum = 'LV-' . date('Ym') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $leaveReq = LeaveRequest::create([
            'request_number' => $reqNum,
            'employee_id' => $request->input('employee_id'),
            'leave_type_id' => $request->input('leave_type_id'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'total_days' => $workdays,
            'reason' => $request->input('reason'),
            'status' => 'submitted',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pengajuan cuti berhasil dikirimkan.',
            'data' => $leaveReq,
        ], 201);
    }

    /**
     * POST /api/v1/leave/requests/{id}/approve
     */
    public function approveLeaveRequest(int $id): JsonResponse
    {
        $leave = LeaveRequest::find($id);
        if (!$leave) {
            return response()->json(['status' => 'error', 'message' => 'Pengajuan tidak ditemukan.'], 404);
        }

        $leave->update(['status' => 'approved']);

        // Update attendance daily summaries
        $start = Carbon::parse($leave->start_date);
        $end = Carbon::parse($leave->end_date);
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if (!$d->isWeekend()) {
                AttendanceDailySummary::updateOrCreate(
                    ['employee_id' => $leave->employee_id, 'date' => $d->format('Y-m-d')],
                    ['status' => 'on_leave', 'notes' => 'Cuti Disetujui (API)']
                );
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Pengajuan cuti {$leave->request_number} telah disetujui.",
            'data' => $leave,
        ]);
    }

    /**
     * POST /api/v1/leave/requests/{id}/reject
     */
    public function rejectLeaveRequest(int $id): JsonResponse
    {
        $leave = LeaveRequest::find($id);
        if (!$leave) {
            return response()->json(['status' => 'error', 'message' => 'Pengajuan tidak ditemukan.'], 404);
        }

        $leave->update(['status' => 'rejected']);

        return response()->json([
            'status' => 'success',
            'message' => "Pengajuan cuti {$leave->request_number} telah ditolak.",
            'data' => $leave,
        ]);
    }

    /**
     * POST /api/v1/overtime/requests
     */
    public function storeOvertimeRequest(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $start = Carbon::parse("{$request->input('date')} {$request->input('start_time')}");
        $end = Carbon::parse("{$request->input('date')} {$request->input('end_time')}");
        if ($end->lte($start)) {
            $end->addDay();
        }
        $hours = round($start->diffInMinutes($end) / 60, 2);

        $ot = OvertimeRequest::create([
            'request_number' => 'OT-' . date('Ym') . '-' . str_pad(OvertimeRequest::count() + 1, 4, '0', STR_PAD_LEFT),
            'employee_id' => $request->input('employee_id'),
            'date' => $request->input('date'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'estimated_hours' => $hours,
            'actual_hours' => $hours,
            'reason' => $request->input('reason'),
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pengajuan lembur berhasil dibuat.',
            'data' => $ot,
        ], 201);
    }

    /**
     * GET /api/v1/payroll/periods
     */
    public function getPayrollPeriods(): JsonResponse
    {
        $periods = PayrollPeriod::withCount('items')->orderBy('start_date', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $periods,
        ]);
    }

    /**
     * POST /api/v1/payroll/runs
     */
    public function runPayroll(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payroll_period_id' => 'required|exists:payroll_periods,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $period = PayrollPeriod::find($request->input('payroll_period_id'));
        if ($period->status === 'locked') {
            return response()->json(['status' => 'error', 'message' => 'Periode payroll ini telah dikunci.'], 400);
        }

        // Trigger calculation controller logic
        $payrollController = app(\App\Http\Controllers\PayrollController::class);
        $payrollController->calculate($period);

        return response()->json([
            'status' => 'success',
            'message' => "Kalkulasi payroll periode {$period->name} berhasil dijalankan.",
            'period' => $period->fresh(['items']),
        ]);
    }

    /**
     * GET /api/v1/reports/attendance
     */
    public function getAttendanceReport(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', Carbon::today()->format('Y-m-d'));

        $query = AttendanceDailySummary::with(['employee.department', 'shift'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($branchId = $request->query('branch_id')) {
            $query->whereHas('employee', fn($q) => $q->where('branch_id', $branchId));
        }

        $summaries = $query->orderBy('date', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'total_records' => $summaries->count(),
            'data' => $summaries,
        ]);
    }

    /**
     * GET /api/v1/devices
     */
    public function getDevices(): JsonResponse
    {
        $devices = Device::with('branch')->withCount(['attendanceLogs', 'employeeMappings'])->get();

        return response()->json([
            'status' => 'success',
            'data' => $devices,
        ]);
    }

    /**
     * POST /api/v1/devices/{id}/sync
     */
    public function syncDevice(int $id): JsonResponse
    {
        $device = Device::find($id);
        if (!$device) {
            return response()->json(['status' => 'error', 'message' => 'Perangkat tidak ditemukan.'], 404);
        }

        $result = $this->connectorService->syncDeviceLogs($device, 'api');
        if ($result['success']) {
            $this->engineService->processDailyAttendance(Carbon::today());
        }

        return response()->json([
            'status' => $result['success'] ? 'success' : 'error',
            'message' => $result['message'],
            'data' => $result,
        ], $result['success'] ? 200 : 500);
    }

    /**
     * GET /api/v1/visits
     * Daftar riwayat kunjungan lapangan karyawan
     */
    public function getVisits(Request $request): JsonResponse
    {
        $query = AttendanceVisit::with(['employee.department', 'branch']);

        if ($empId = $request->query('employee_id')) {
            $query->where('employee_id', $empId);
        }
        if ($date = $request->query('date')) {
            $query->whereDate('check_in_at', $date);
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $visits = $query->orderBy('check_in_at', 'desc')->paginate($request->query('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $visits,
        ]);
    }

    /**
     * GET /api/v1/visits/active
     * Cek kunjungan yang sedang aktif (ongoing) untuk karyawan
     */
    public function getActiveVisit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $active = AttendanceVisit::where('employee_id', $request->query('employee_id'))
            ->where('status', 'ongoing')
            ->first();

        return response()->json([
            'status' => 'success',
            'has_active_visit' => $active !== null,
            'data' => $active,
        ]);
    }

    /**
     * POST /api/v1/visits/check-in
     * Check-in kunjungan lapangan baru (bisa berkali-kali, asalkan tidak ada kunjungan ongoing)
     */
    public function visitCheckIn(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'client_name' => 'required|string|max:150',
            'purpose' => 'required|string|max:1000',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $employee = Employee::with('branch')->find($request->input('employee_id'));

        // CRITICAL RULE: Cek apakah masih ada kunjungan aktif yang belum di-checkout
        $ongoing = AttendanceVisit::where('employee_id', $employee->id)
            ->where('status', 'ongoing')
            ->first();

        if ($ongoing) {
            return response()->json([
                'status' => 'error',
                'message' => "Anda masih memiliki kunjungan aktif di '{$ongoing->client_name}' (Check-in: {$ongoing->check_in_at->format('H:i')}). Anda wajib melakukan Check-Out terlebih dahulu sebelum dapat memulai kunjungan baru!",
                'active_visit' => $ongoing,
            ], 422);
        }

        $branch = $employee->branch;
        $tz = $branch?->timezone ?: 'Asia/Jakarta';
        $localNow = Carbon::now($tz);

        $todayStr = $localNow->format('Ymd');
        $countToday = AttendanceVisit::whereDate('created_at', $localNow->format('Y-m-d'))->count();
        $visitNumber = 'VST-' . $todayStr . '-' . str_pad($countToday + 1, 4, '0', STR_PAD_LEFT);

        $visit = AttendanceVisit::create([
            'employee_id' => $employee->id,
            'branch_id' => $branch?->id,
            'visit_number' => $visitNumber,
            'client_name' => $request->input('client_name'),
            'purpose' => $request->input('purpose'),
            'check_in_at' => $localNow->format('Y-m-d H:i:s'),
            'check_in_lat' => $request->input('latitude'),
            'check_in_lon' => $request->input('longitude'),
            'check_in_address' => $request->input('address'),
            'timezone' => $tz,
            'status' => 'ongoing',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Check-in kunjungan di '{$visit->client_name}' berhasil dicatat.",
            'data' => $visit,
        ], 201);
    }

    /**
     * POST /api/v1/visits/{id}/check-out
     * Check-out kunjungan lapangan
     */
    public function visitCheckOut(Request $request, int $id): JsonResponse
    {
        $visit = AttendanceVisit::find($id);

        if (!$visit) {
            return response()->json(['status' => 'error', 'message' => 'Data kunjungan tidak ditemukan.'], 404);
        }

        if ($visit->status !== 'ongoing') {
            return response()->json(['status' => 'error', 'message' => 'Kunjungan ini sudah selesai di-checkout atau tidak berstatus aktif.'], 422);
        }

        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'nullable|string|max:500',
            'outcome' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $tz = $visit->timezone ?: 'Asia/Jakarta';
        $localNow = Carbon::now($tz);
        $durationMinutes = max(1, $visit->check_in_at->diffInMinutes($localNow));

        $visit->update([
            'check_out_at' => $localNow->format('Y-m-d H:i:s'),
            'check_out_lat' => $request->input('latitude'),
            'check_out_lon' => $request->input('longitude'),
            'check_out_address' => $request->input('address'),
            'outcome' => $request->input('outcome'),
            'duration_minutes' => $durationMinutes,
            'status' => 'completed',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Check-out kunjungan '{$visit->client_name}' berhasil dicatat! Durasi: {$visit->formatted_duration}.",
            'data' => $visit,
        ]);
    }
}
