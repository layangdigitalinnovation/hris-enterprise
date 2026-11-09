<?php

namespace App\Http\Controllers;

use App\Models\AttendanceAdjustment;
use App\Models\AttendanceDailySummary;
use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Services\Attendance\AttendanceEngineService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceEngineService $engine
    ) {}

    public function index(Request $request)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));

        $query = AttendanceDailySummary::with(['employee.department', 'employee.position', 'shift'])
            ->where('date', $date);

        // Scope by Role
        if ($currentUser && $currentUser->isEmployee()) {
            $employeeId = $currentUser->employee?->id ?? 0;
            $query->where('employee_id', $employeeId);
        } elseif ($currentUser && $currentUser->role === 'manager' && $currentUser->employee?->department_id) {
            $deptId = $currentUser->employee->department_id;
            $query->whereHas('employee', fn($q) => $q->where('department_id', $deptId));
        }

        if ($dept = $request->input('department_id')) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $dept));
        }

        if ($branch = $request->input('branch_id')) {
            $query->whereHas('employee', fn($q) => $q->where('branch_id', $branch));
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $summaries = $query->orderBy('clock_in', 'asc')->paginate(15)->withQueryString();

        // Stats for selected date scoped
        $statsQuery = AttendanceDailySummary::where('date', $date);
        if ($currentUser && $currentUser->isEmployee()) {
            $statsQuery->where('employee_id', $currentUser->employee?->id ?? 0);
        } elseif ($currentUser && $currentUser->role === 'manager' && $currentUser->employee?->department_id) {
            $statsQuery->whereHas('employee', fn($q) => $q->where('department_id', $currentUser->employee->department_id));
        }

        $allSums = $statsQuery->get();
        $present = $allSums->whereIn('status', ['present', 'early_leave'])->count();
        $late = $allSums->where('status', 'late')->count();
        $absent = $allSums->where('status', 'absent')->count();
        $onLeave = $allSums->where('status', 'on_leave')->count();

        $departments = Department::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();

        return view('attendance.index', compact(
            'summaries',
            'date',
            'present',
            'late',
            'absent',
            'onLeave',
            'departments',
            'branches'
        ));
    }

    public function recalculate(Request $request)
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Hanya HR Admin atau Super Admin yang dapat memicu kalkulasi ulang absensi.');
        }

        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $result = $this->engine->processDailyAttendance($date);

        return back()->with('success', $result['message']);
    }

    public function adjustments(Request $request)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();

        $query = AttendanceAdjustment::with(['employee.department', 'approver']);

        // Scope by Role
        if ($currentUser && $currentUser->isEmployee()) {
            $employeeId = $currentUser->employee?->id ?? 0;
            $query->where('employee_id', $employeeId);
        } elseif ($currentUser && $currentUser->role === 'manager' && $currentUser->employee?->department_id) {
            $deptId = $currentUser->employee->department_id;
            $query->whereHas('employee', fn($q) => $q->where('department_id', $deptId));
        }

        // Search Filter (Employee Name, Code, Department, Reason, Date)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('employee', function ($eq) use ($search) {
                    $eq->where('first_name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%")
                       ->orWhere('employee_code', 'like', "%{$search}%")
                       ->orWhereHas('department', function ($dq) use ($search) {
                           $dq->where('name', 'like', "%{$search}%");
                       });
                })->orWhere('reason', 'like', "%{$search}%")
                  ->orWhere('date', 'like', "%{$search}%");
            });
        }

        // Status Filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Month Filter
        if ($month = $request->input('month')) {
            $query->where('date', 'like', "{$month}%");
        }

        $adjustments = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('attendance.adjustments', compact('adjustments'));
    }

    public function createAdjustment()
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();

        $empListQuery = Employee::where('status', 'active');
        if ($currentUser && $currentUser->isEmployee()) {
            $empListQuery->where('id', $currentUser->employee?->id ?? 0);
        } elseif ($currentUser && $currentUser->role === 'manager' && $currentUser->employee?->department_id) {
            $empListQuery->where('department_id', $currentUser->employee->department_id);
        }
        $employees = $empListQuery->orderBy('first_name')->orderBy('last_name')->get();

        return view('attendance.adjustments_create', compact('employees'));
    }

    public function storeAdjustment(Request $request)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'requested_clock_in' => 'nullable|date_format:H:i',
            'requested_clock_out' => 'nullable|date_format:H:i',
            'reason' => 'required|string|max:500',
        ]);

        // If regular employee, always enforce own employee ID
        if ($currentUser && $currentUser->isEmployee()) {
            if (!$currentUser->employee) {
                abort(403, 'Akun Anda belum terhubung dengan profil karyawan.');
            }
            $validated['employee_id'] = $currentUser->employee->id;
        }

        AttendanceAdjustment::create($validated);

        return redirect()->route('attendance.adjustments')->with('success', 'Pengajuan koreksi absensi berhasil dikirimkan.');
    }

    public function approveAdjustment(Request $request, AttendanceAdjustment $adjustment)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();

        if (!$currentUser || !$currentUser->canApprove()) {
            abort(403, 'Akses ditolak. Hanya Manager, HR Admin, atau Super Admin yang berhak menyetujui atau menolak koreksi presensi.');
        }

        // Manager can only approve employees in their own department
        if ($currentUser->role === 'manager' && $currentUser->employee) {
            if ($adjustment->employee->department_id !== $currentUser->employee->department_id) {
                abort(403, 'Akses ditolak. Anda hanya berwenang menyetujui koreksi presensi karyawan di departemen Anda.');
            }
        }

        $status = $request->input('action') === 'reject' ? 'rejected' : 'approved';

        $adjustment->update([
            'status' => $status,
            'approved_by' => $currentUser->id,
            'approved_at' => now(),
            'approval_notes' => $request->input('approval_notes'),
        ]);

        if ($status === 'approved') {
            // Update attendance daily summary with approved times
            AttendanceDailySummary::updateOrCreate(
                [
                    'employee_id' => $adjustment->employee_id,
                    'date' => $adjustment->date,
                ],
                [
                    'clock_in' => $adjustment->requested_clock_in ? "{$adjustment->requested_clock_in}:00" : null,
                    'clock_out' => $adjustment->requested_clock_out ? "{$adjustment->requested_clock_out}:00" : null,
                    'status' => 'present',
                    'notes' => 'Koreksi absensi disetujui: ' . $adjustment->reason,
                ]
            );
        }

        return back()->with('success', "Pengajuan koreksi absensi berhasil di-" . ($status === 'approved' ? 'setujui' : 'tolak') . ".");
    }

    public function exportExcel(Request $request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $month = $request->input('month');
        $summaries = $this->getFilteredSummaries($request);

        $present = $summaries->whereIn('status', ['present', 'early_leave'])->count();
        $late = $summaries->where('status', 'late')->count();
        $absent = $summaries->where('status', 'absent')->count();
        $onLeave = $summaries->whereIn('status', ['on_leave', 'sick', 'permit'])->count();

        $selectedDept = null;
        if ($deptId = $request->input('department_id')) {
            $selectedDept = Department::find($deptId)?->name;
        }

        $filename = ($month ? "Rekap_Presensi_{$month}" : "Rekap_Presensi_{$date}") . '_' . date('His') . '.xlsx';

        $companyName = \App\Models\SystemSetting::get('company_name', 'PT Solusi Teknologi Nusantara');
        $printedAt = Carbon::now()->locale(app()->getLocale())->translatedFormat('d F Y H:i');
        $printedBy = auth()->user()->name ?? 'Administrator';
        $dateFormatted = Carbon::parse($date)->locale(app()->getLocale())->translatedFormat('l, d F Y');
        $signers = $this->getSigners($request);

        $rows = [];
        foreach ($summaries as $s) {
            $rows[] = [
                'date' => $s->date ? $s->date->format('Y-m-d') : '-',
                'code' => $s->employee->employee_code ?? '-',
                'name' => $s->employee->full_name ?? '-',
                'department' => $s->employee->department->name ?? '-',
                'shift' => $s->shift->name ?? 'Reguler',
                'clock_in' => $s->clock_in ? substr($s->clock_in, 0, 5) : '--:--',
                'clock_out' => $s->clock_out ? substr($s->clock_out, 0, 5) : '--:--',
                'late' => $s->late_minutes > 0 ? $s->late_minutes . ' ' . __('Menit') : '-',
                'work_hours' => $s->work_hours > 0 ? number_format($s->work_hours, 2) . ' ' . __('Jam') : '-',
                'status' => $s->status,
                'status_label' => ($s->status === 'absent') ? 'Alpa' : ($s->status_badge['label'] ?? strtoupper($s->status)),
                'notes' => $s->notes ?: '-',
            ];
        }

        $filePath = \App\Services\Export\AttendanceXlsxService::generate(
            $rows,
            $companyName,
            $dateFormatted,
            $selectedDept,
            $printedAt,
            $signers['maker_name'],
            $signers['maker_title'],
            $signers['approver_name'],
            $signers['approver_title'],
            $present,
            $late,
            $onLeave,
            $absent
        );

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function exportPdf(Request $request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $month = $request->input('month');
        $summaries = $this->getFilteredSummaries($request);

        $present = $summaries->whereIn('status', ['present', 'early_leave'])->count();
        $late = $summaries->where('status', 'late')->count();
        $absent = $summaries->where('status', 'absent')->count();
        $onLeave = $summaries->whereIn('status', ['on_leave', 'sick', 'permit'])->count();

        $selectedDept = null;
        if ($deptId = $request->input('department_id')) {
            $selectedDept = Department::find($deptId)?->name;
        }

        $selectedStatus = $request->input('status');

        $companyName = \App\Models\SystemSetting::get('company_name', 'PT Solusi Teknologi Nusantara');
        $companyAddress = \App\Models\SystemSetting::get('company_address', 'Kantor Pusat');
        $companyPhone = \App\Models\SystemSetting::get('company_phone', '');
        $printedAt = Carbon::now()->translatedFormat('d F Y H:i');
        $dateFormatted = Carbon::parse($date)->translatedFormat('l, d F Y');
        $signers = $this->getSigners($request);
        $printedBy = $signers['maker_name'];

        $filename = ($month ? "Rekap_Presensi_{$month}" : "Rekap_Presensi_{$date}") . '_' . date('His') . '.pdf';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('attendance.pdf', compact(
            'summaries', 'date', 'month', 'companyName', 'companyAddress', 'companyPhone',
            'selectedDept', 'selectedStatus', 'printedAt', 'dateFormatted', 'signers', 'printedBy',
            'present', 'late', 'absent', 'onLeave'
        ))->setPaper('a4', 'landscape');

        if ($request->boolean('preview')) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }

    public function exportCsv(Request $request)
    {
        return $this->exportExcel($request);
    }

    protected function getSigners(Request $request): array
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $employee = $user?->employee;

        // 1. Dibuat Oleh (Penyusun Laporan Presensi - membaca akun yang mencetak & jabatannya)
        $makerName = $employee ? $employee->full_name : ($user->name ?? 'Administrator');
        $makerTitle = $employee?->position?->title 
            ?? ($user->role === 'super_admin' ? 'Super Administrator' : ($user->role === 'hr_admin' ? 'HR Administrator' : 'Staff HR & Presensi'));

        // 2. Mengetahui & Menyetujui (Pejabat Berwenang Sesuai Tupoksi)
        $customApproverName = \App\Models\SystemSetting::get('report_approver_name');
        $customApproverTitle = \App\Models\SystemSetting::get('report_approver_title');

        if ($customApproverName && $customApproverTitle) {
            $approverName = $customApproverName;
            $approverTitle = $customApproverTitle;
        } else {
            // Jika difilter per departemen spesifik, cek apakah ada Manager Departemen terkait
            $deptId = $request->input('department_id');
            $dept = $deptId ? Department::with('manager.position')->find($deptId) : null;

            if ($dept && $dept->manager) {
                $approverName = $dept->manager->full_name;
                $approverTitle = $dept->manager->position?->title ?? ("Manager " . $dept->name);
            } else {
                // Default Tupoksi untuk Rekap Presensi Umum Perusahaan: HR Manager
                $hrManager = Employee::where('status', 'active')
                    ->whereHas('position', fn($q) => $q->where('code', 'MGR-HR')->orWhere('title', 'like', '%HR Manager%')->orWhere('title', 'like', '%Head of HR%'))
                    ->with('position')
                    ->first();

                if (!$hrManager) {
                    $hrManager = Employee::where('status', 'active')
                        ->whereHas('department', fn($q) => $q->where('code', 'HRGA')->orWhere('name', 'like', '%Human Resource%'))
                        ->whereHas('position', fn($q) => $q->where('level', '>=', 3))
                        ->with('position')
                        ->first();
                }

                if ($hrManager) {
                    $approverName = $hrManager->full_name;
                    $approverTitle = $hrManager->position?->title ?? 'HR Manager';
                } else {
                    $approverName = 'Siti Rahmawati';
                    $approverTitle = 'HR Manager';
                }
            }
        }

        return [
            'maker_name' => $makerName,
            'maker_title' => $makerTitle,
            'approver_name' => $approverName,
            'approver_title' => $approverTitle,
        ];
    }

    protected function getFilteredSummaries(Request $request)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $month = $request->input('month');

        $query = AttendanceDailySummary::with(['employee.department', 'employee.position', 'shift']);

        // Scope by Role
        if ($currentUser && $currentUser->isEmployee()) {
            $query->where('employee_id', $currentUser->employee?->id ?? 0);
        } elseif ($currentUser && $currentUser->role === 'manager' && $currentUser->employee?->department_id) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $currentUser->employee->department_id));
        }

        if ($month) {
            $query->where('date', 'like', "{$month}%");
        } else {
            $query->where('date', $date);
        }

        if ($dept = $request->input('department_id')) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $dept));
        }

        if ($branch = $request->input('branch_id')) {
            $query->whereHas('employee', fn($q) => $q->where('branch_id', $branch));
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        return $query->orderBy('date', 'desc')->orderBy('clock_in', 'asc')->get();
    }

    /**
     * Presensi GPS Mandiri (Clock In / Clock Out) dengan validasi radius geofence dan zona waktu kantor cabang.
     */
    public function gpsClockIn(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'log_type' => 'nullable|in:check_in,check_out',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        $user = auth()->user();

        // Support admin simulation or current employee
        $employee = null;
        if ($request->filled('employee_id') && $user->canManageEmployees()) {
            $employee = Employee::with('branch')->find($request->employee_id);
        } else {
            $employee = $user->employee?->load('branch');
        }

        if (!$employee) {
            $msg = __('Akun pengguna Anda belum terhubung dengan data karyawan operasional.');
            return $request->wantsJson() ? response()->json(['success' => false, 'message' => $msg], 422) : back()->with('error', $msg);
        }

        $branch = $employee->branch;
        if (!$branch) {
            $msg = __('Karyawan :name belum ditempatkan pada kantor cabang operasional manapun.', ['name' => $employee->full_name]);
            return $request->wantsJson() ? response()->json(['success' => false, 'message' => $msg], 422) : back()->with('error', $msg);
        }

        if (!$branch->hasCoordinates()) {
            $msg = __('Kantor cabang :branch belum memiliki koordinat GPS untuk validasi geofencing.', ['branch' => $branch->name]);
            return $request->wantsJson() ? response()->json(['success' => false, 'message' => $msg], 422) : back()->with('error', $msg);
        }

        $lat = (float) $request->latitude;
        $lon = (float) $request->longitude;
        $check = $branch->isWithinRadius($lat, $lon);

        if (!$check['allowed']) {
            $msg = __('Presensi GPS ditolak! Anda berada sejauh :dist m dari :branch (batas maksimum radius :rad m).', [
                'dist' => $check['distance'],
                'branch' => $branch->name,
                'rad' => $check['radius'],
            ]);
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $msg, 'data' => $check], 422)
                : back()->with('error', $msg);
        }

        // Branch local time (respecting Indonesia WIB/WITA/WIT or Saudi Arabia AST)
        $tz = $branch->timezone ?: 'Asia/Jakarta';
        $localNow = Carbon::now($tz);
        $type = $request->input('log_type', 'check_in');

        // Check if device mapping exists or use employee code
        $pin = (string) ($employee->deviceMappings()->first()?->device_pin ?? $employee->employee_code);

        $hashSig = hash('sha256', "gps_{$employee->id}_{$localNow->format('Ymd_His')}_{$type}");

        $log = AttendanceLog::create([
            'employee_id' => $employee->id,
            'device_pin' => $pin,
            'log_time' => $localNow->format('Y-m-d H:i:s'),
            'verify_type' => 'gps',
            'log_type' => $type,
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
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
            'hash_signature' => $hashSig,
            'status' => 'pending',
        ]);

        // Process daily attendance in engine using the branch local date
        $this->engine->processDailyAttendance($localNow->format('Y-m-d'), $employee->id);

        $typeLabel = $type === 'check_in' ? __('Presensi Masuk (Clock In)') : __('Presensi Keluar (Clock Out)');
        $successMsg = __(':type berhasil dicatat di :branch (:flag :tz)! Waktu: :time, Jarak dari kantor: :dist m.', [
            'type' => $typeLabel,
            'branch' => $branch->name,
            'flag' => $branch->country_flag,
            'tz' => $tz,
            'time' => $localNow->format('H:i:s'),
            'dist' => $check['distance'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'data' => [
                    'log_id' => $log->id,
                    'time' => $localNow->format('Y-m-d H:i:s'),
                    'timezone' => $tz,
                    'distance_meters' => $check['distance'],
                    'branch' => $branch->name,
                    'country' => $branch->country_name,
                ],
            ]);
        }

        return back()->with('success', $successMsg);
    }

    /**
     * Cek jarak koordinat terhadap radius kantor cabang tanpa menyimpan absensi (Live Verification).
     */
    public function checkRadius(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'branch_id' => 'nullable|exists:branches,id',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        $branch = null;
        if ($request->filled('branch_id')) {
            $branch = Branch::find($request->branch_id);
        } elseif ($request->filled('employee_id')) {
            $emp = Employee::with('branch')->find($request->employee_id);
            $branch = $emp?->branch;
        } else {
            $branch = auth()->user()->employee?->branch;
        }

        if (!$branch) {
            return response()->json([
                'success' => false,
                'message' => __('Kantor cabang tidak ditemukan.'),
            ], 404);
        }

        $check = $branch->isWithinRadius((float) $request->latitude, (float) $request->longitude);
        $localTime = $branch->getLocalTime();

        return response()->json([
            'success' => true,
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
        ]);
    }
}
