<?php

namespace App\Http\Controllers;

use App\Helpers\ArabicPdfHelper;
use App\Models\AttendanceDailySummary;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeLoan;
use App\Models\EmployeeLoanInstallment;
use App\Models\EmployeeShiftAssignment;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\ReimbursementCategory;
use App\Models\ReimbursementRequest;
use App\Models\Shift;
use App\Models\SystemSetting;
use App\Services\Export\ReportXlsxService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // ==========================================
    // 1. LAPORAN PRESENSI
    // ==========================================
    public function attendance(Request $request)
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();

        $currentMonth = date('m');
        $currentYear = date('Y');
        $startYear = 2022;

        return view('reports.attendance', compact(
            'branches',
            'departments',
            'employees',
            'currentMonth',
            'currentYear',
            'startYear'
        ));
    }

    public function exportAttendance(Request $request)
    {
        $action = $request->input('action_type', 'pdf'); // 'pdf' or 'excel'
        $mode = $request->input('periode_laporan', '1'); // 1: cut-off, 2: monthly, 3: custom date range
        $bulan = (int)$request->input('bulan', date('m'));
        $tahun = (int)$request->input('tahun', date('Y'));

        // Resolve Date Range
        if ($mode == '1') {
            // Cut-Off Payroll (Default: 26 bulan sebelumnya s/d 25 bulan berjalan)
            $cutOffDay = (int)SystemSetting::get('payroll_cutoff_day', 25);
            $startDate = Carbon::create($tahun, $bulan, $cutOffDay + 1)->subMonth()->format('Y-m-d');
            $endDate = Carbon::create($tahun, $bulan, $cutOffDay)->format('Y-m-d');
            $periodLabel = __('Periode Cut-Off: :start - :end', [
                'start' => Carbon::parse($startDate)->format('d/m/Y'),
                'end' => Carbon::parse($endDate)->format('d/m/Y'),
            ]);
        } elseif ($mode == '2') {
            // Bulan Berjalan (01 s/d akhir bulan)
            $startDate = Carbon::create($tahun, $bulan, 1)->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');
            $periodLabel = __('Bulan Berjalan: :month', [
                'month' => Carbon::create($tahun, $bulan, 1)->locale(app()->getLocale())->translatedFormat('F Y'),
            ]);
        } else {
            // Custom Date Range
            $startDate = $request->input('dari', date('Y-m-01'));
            $endDate = $request->input('sampai', date('Y-m-d'));
            $periodLabel = __('Rentang: :start - :end', [
                'start' => Carbon::parse($startDate)->format('d/m/Y'),
                'end' => Carbon::parse($endDate)->format('d/m/Y'),
            ]);
        }

        // Build Query
        $query = AttendanceDailySummary::with(['employee.department', 'employee.branch', 'employee.position', 'shift'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($branchId = $request->input('kode_cabang')) {
            $query->whereHas('employee', fn($q) => $q->where('branch_id', $branchId));
        }

        if ($deptId = $request->input('kode_dept')) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $deptId));
        }

        if ($nik = $request->input('nik')) {
            $query->where('employee_id', $nik);
        }

        $summaries = $query->orderBy('date', 'asc')->orderBy('employee_id', 'asc')->get();

        // Meta Data
        $companyName = SystemSetting::get('company_name', 'PT Solusi Teknologi Nusantara');
        $companyAddress = SystemSetting::get('company_address', 'Kantor Pusat');
        $companyPhone = SystemSetting::get('company_phone', '');
        $printedAt = Carbon::now()->locale(app()->getLocale())->translatedFormat('d F Y H:i');
        $signers = $this->getSigners($request);

        $selectedBranch = $request->kode_cabang ? Branch::find($request->kode_cabang)?->name : 'Semua Cabang';
        $selectedDept = $request->kode_dept ? Department::find($request->kode_dept)?->name : 'Semua Departemen';
        $selectedEmployee = $request->nik ? Employee::find($request->nik)?->full_name : 'Semua Karyawan';

        // Stats
        $present = $summaries->whereIn('status', ['present', 'early_leave'])->count();
        $late = $summaries->where('status', 'late')->count();
        $onLeave = $summaries->whereIn('status', ['on_leave', 'sick', 'permit'])->count();
        $absent = $summaries->where('status', 'absent')->count();

        $filename = "Laporan_Presensi_" . str_replace('-', '', $startDate) . '_' . str_replace('-', '', $endDate) . '_' . date('His');

        if ($action === 'excel' || $request->has('exportButton')) {
            return ReportXlsxService::downloadAttendance(
                $summaries, $companyName, $periodLabel, $selectedBranch, $selectedDept,
                $printedAt, $signers, $present, $late, $onLeave, $absent, $filename . '.xlsx'
            );
        }

        // Generate PDF
        $pdf = ArabicPdfHelper::loadView('reports.pdf.attendance', compact(
            'summaries', 'startDate', 'endDate', 'periodLabel', 'companyName', 'companyAddress', 'companyPhone',
            'selectedBranch', 'selectedDept', 'selectedEmployee', 'printedAt', 'signers',
            'present', 'late', 'absent', 'onLeave'
        ), 'a4', 'landscape');

        return $pdf->download($filename . '.pdf');
    }

    // ==========================================
    // 2. LAPORAN GAJI & REKAP PAYROLL
    // ==========================================
    public function payroll(Request $request)
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();
        $payrollPeriods = PayrollPeriod::orderBy('end_date', 'desc')->get();

        $currentMonth = date('m');
        $currentYear = date('Y');
        $startYear = 2022;

        return view('reports.payroll', compact(
            'branches',
            'departments',
            'employees',
            'payrollPeriods',
            'currentMonth',
            'currentYear',
            'startYear'
        ));
    }

    public function exportPayroll(Request $request)
    {
        $action = $request->input('action_type', 'pdf');
        $periodId = $request->input('payroll_period_id');
        $bulan = (int)$request->input('bulan', date('m'));
        $tahun = (int)$request->input('tahun', date('Y'));

        $query = PayrollItem::with(['employee.department', 'employee.branch', 'employee.position', 'period']);

        if ($periodId) {
            $query->where('payroll_period_id', $periodId);
            $period = PayrollPeriod::find($periodId);
            $periodLabel = $period ? $period->name . " (" . $period->start_date->format('d/m/Y') . " - " . $period->end_date->format('d/m/Y') . ")" : "Periode #{$periodId}";
        } else {
            // Find periods matching year and month or date range
            $query->whereHas('period', function ($q) use ($tahun, $bulan) {
                $q->whereYear('end_date', $tahun)->whereMonth('end_date', $bulan);
            });
            $periodLabel = __('Periode :period', [
                'period' => Carbon::create($tahun, $bulan, 1)->locale(app()->getLocale())->translatedFormat('F Y'),
            ]);
        }

        if ($branchId = $request->input('kode_cabang')) {
            $query->whereHas('employee', fn($q) => $q->where('branch_id', $branchId));
        }

        if ($deptId = $request->input('kode_dept')) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $deptId));
        }

        if ($employeeId = $request->input('nik')) {
            $query->where('employee_id', $employeeId);
        }

        $items = $query->orderBy('employee_id')->get();

        $companyName = SystemSetting::get('company_name', 'PT Solusi Teknologi Nusantara');
        $companyAddress = SystemSetting::get('company_address', 'Kantor Pusat');
        $companyPhone = SystemSetting::get('company_phone', '');
        $printedAt = Carbon::now()->locale(app()->getLocale())->translatedFormat('d F Y H:i');
        $signers = $this->getSigners($request);

        $selectedBranch = $request->kode_cabang ? Branch::find($request->kode_cabang)?->name : 'Semua Cabang';
        $selectedDept = $request->kode_dept ? Department::find($request->kode_dept)?->name : 'Semua Departemen';
        $selectedEmployee = $request->nik ? Employee::find($request->nik)?->full_name : 'Semua Karyawan';

        $totalBasic = $items->sum('basic_salary');
        $totalAllowances = $items->sum('total_allowances');
        $totalOvertime = $items->sum('total_overtime');
        $totalDeductions = $items->sum('total_deductions');
        $totalNet = $items->sum('net_salary');

        $filename = "Laporan_Gaji_" . $tahun . '_' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '_' . date('His');

        if ($action === 'excel' || $request->has('exportButton')) {
            return ReportXlsxService::downloadPayroll(
                $items, $companyName, $periodLabel, $selectedBranch, $selectedDept,
                $printedAt, $signers, $totalBasic, $totalAllowances, $totalOvertime, $totalDeductions, $totalNet, $filename . '.xlsx'
            );
        }

        $pdf = ArabicPdfHelper::loadView('reports.pdf.payroll', compact(
            'items', 'periodLabel', 'companyName', 'companyAddress', 'companyPhone',
            'selectedBranch', 'selectedDept', 'selectedEmployee', 'printedAt', 'signers',
            'totalBasic', 'totalAllowances', 'totalOvertime', 'totalDeductions', 'totalNet'
        ), 'a4', 'landscape');

        return $pdf->download($filename . '.pdf');
    }

    // ==========================================
    // 3. LAPORAN & MATRIKS JADWAL KERJA
    // ==========================================
    public function schedule(Request $request)
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();
        $shifts = Shift::where('is_active', true)->orderBy('start_time')->get();

        $defaultDari = Carbon::now()->startOfWeek()->format('Y-m-d');
        $defaultSampai = Carbon::now()->endOfWeek()->format('Y-m-d');

        return view('reports.schedule', compact(
            'branches',
            'departments',
            'employees',
            'shifts',
            'defaultDari',
            'defaultSampai'
        ));
    }

    public function exportSchedule(Request $request)
    {
        $action = $request->input('action_type', 'pdf');
        $dari = $request->input('dari', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $sampai = $request->input('sampai', Carbon::now()->endOfWeek()->format('Y-m-d'));

        $start = Carbon::parse($dari);
        $end = Carbon::parse($sampai);
        if ($start->gt($end)) {
            $tmp = $start;
            $start = $end;
            $end = $tmp;
            $dari = $start->format('Y-m-d');
            $sampai = $end->format('Y-m-d');
        }

        $dates = [];
        $period = CarbonPeriod::create($start, $end);
        foreach ($period as $dt) {
            $dates[] = $dt->format('Y-m-d');
        }

        // Build Chunks for PDF Rendering (Paging per Month if > 31 days to ensure full legibility)
        $pdfChunks = [];
        if (count($dates) <= 31) {
            $pdfChunks[] = [
                'label' => Carbon::parse($dari)->format('d/m/Y') . ' - ' . Carbon::parse($sampai)->format('d/m/Y'),
                'dates' => $dates,
            ];
        } else {
            $byMonth = [];
            foreach ($dates as $d) {
                $mKey = date('Y-m', strtotime($d));
                $byMonth[$mKey][] = $d;
            }
            foreach ($byMonth as $mKey => $mDates) {
                $mStart = Carbon::parse($mDates[0]);
                $mEnd = Carbon::parse(end($mDates));
                $pdfChunks[] = [
                    'label' => $mStart->translatedFormat('F Y') . ' (' . $mStart->format('d/m/Y') . ' - ' . $mEnd->format('d/m/Y') . ')',
                    'dates' => $mDates,
                ];
            }
        }

        // Query Employees
        $empQuery = Employee::with(['department', 'branch', 'position'])
            ->where('status', 'active');

        if ($branchId = $request->input('kode_cabang')) {
            $empQuery->where('branch_id', $branchId);
        }

        if ($deptId = $request->input('kode_dept')) {
            $empQuery->where('department_id', $deptId);
        }

        if ($employeeId = $request->input('nik')) {
            $empQuery->where('id', $employeeId);
        }

        $employees = $empQuery->orderBy('first_name')->get();

        // Query Schedule Assignments
        $assignments = EmployeeShiftAssignment::with('shift')
            ->whereIn('employee_id', $employees->pluck('id'))
            ->whereBetween('date', [$dari, $sampai])
            ->get()
            ->groupBy(fn($item) => $item->employee_id . '|' . $item->date->format('Y-m-d'));

        // Query Attendance summaries as fallback shift info
        $dailySummaries = AttendanceDailySummary::with('shift')
            ->whereIn('employee_id', $employees->pluck('id'))
            ->whereBetween('date', [$dari, $sampai])
            ->get()
            ->groupBy(fn($item) => $item->employee_id . '|' . $item->date->format('Y-m-d'));

        // Build Matrix Data: array of [employee, dates => [date => shift_code/off]]
        $matrix = [];
        foreach ($employees as $emp) {
            $rowDates = [];
            foreach ($dates as $d) {
                $key = $emp->id . '|' . $d;
                $assignment = $assignments->get($key)?->first();
                $summary = $dailySummaries->get($key)?->first();

                if ($assignment) {
                    $code = $assignment->is_day_off ? 'OFF' : $this->cleanShiftCode($assignment->shift?->code ?? 'S');
                    $rowDates[$d] = [
                        'code' => $code,
                        'name' => $assignment->is_day_off ? 'Libur' : ($assignment->shift?->name ?? 'Shift'),
                        'color' => $assignment->is_day_off ? '#ef4444' : ($assignment->shift?->color ?? '#3b82f6'),
                        'time' => $assignment->is_day_off ? '-' : substr($assignment->shift?->start_time ?? '', 0, 5) . '-' . substr($assignment->shift?->end_time ?? '', 0, 5),
                    ];
                } elseif ($summary && $summary->shift) {
                    $code = $this->cleanShiftCode($summary->shift->code ?? 'S');
                    $rowDates[$d] = [
                        'code' => $code,
                        'name' => $summary->shift->name ?? 'Shift',
                        'color' => $summary->shift->color ?? '#3b82f6',
                        'time' => substr($summary->shift->start_time, 0, 5) . '-' . substr($summary->shift->end_time, 0, 5),
                    ];
                } else {
                    // Regular fallback
                    $dayOfWeek = Carbon::parse($d)->dayOfWeek;
                    $isWeekend = ($dayOfWeek == 0 || $dayOfWeek == 6);
                    $rowDates[$d] = [
                        'code' => $isWeekend ? 'OFF' : 'REG',
                        'name' => $isWeekend ? 'Libur Akhir Pekan' : 'Reguler',
                        'color' => $isWeekend ? '#ef4444' : '#10b981',
                        'time' => $isWeekend ? '-' : '08:00-17:00',
                    ];
                }
            }

            $matrix[] = [
                'employee' => $emp,
                'dates' => $rowDates,
            ];
        }

        $companyName = SystemSetting::get('company_name', 'PT Solusi Teknologi Nusantara');
        $companyAddress = SystemSetting::get('company_address', 'Kantor Pusat');
        $companyPhone = SystemSetting::get('company_phone', '');
        $printedAt = Carbon::now()->locale(app()->getLocale())->translatedFormat('d F Y H:i');
        $periodLabel = __('Rentang: :start - :end', [
            'start' => Carbon::parse($dari)->format('d/m/Y'),
            'end' => Carbon::parse($sampai)->format('d/m/Y'),
        ]);
        $signers = $this->getSigners($request);

        $selectedBranch = $request->kode_cabang ? Branch::find($request->kode_cabang)?->name : 'Semua Cabang';
        $selectedDept = $request->kode_dept ? Department::find($request->kode_dept)?->name : 'Semua Departemen';
        $shifts = Shift::where('is_active', true)->get();

        $filename = "Laporan_Jadwal_" . str_replace('-', '', $dari) . '_' . str_replace('-', '', $sampai) . '_' . date('His');

        if ($action === 'excel' || $request->has('exportButton')) {
            return ReportXlsxService::downloadSchedule(
                $matrix, $dates, $shifts, $companyName, $periodLabel, $selectedBranch, $selectedDept,
                $printedAt, $signers, $filename . '.xlsx'
            );
        }

        $pdf = ArabicPdfHelper::loadView('reports.pdf.schedule', compact(
            'matrix', 'dates', 'pdfChunks', 'shifts', 'dari', 'sampai', 'periodLabel',
            'companyName', 'companyAddress', 'companyPhone',
            'selectedBranch', 'selectedDept', 'printedAt', 'signers'
        ), 'a4', 'landscape');

        return $pdf->download($filename . '.pdf');
    }

    // ==========================================
    // 4. LAPORAN REKAPITULASI CUTI
    // ==========================================
    public function leave(Request $request)
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();

        $currentYear = date('Y');
        $years = range($currentYear, 2022);

        return view('reports.leave', compact(
            'branches',
            'departments',
            'leaveTypes',
            'currentYear',
            'years'
        ));
    }

    public function exportLeave(Request $request)
    {
        $action = $request->input('action_type', 'pdf');
        $tahun = (int)$request->input('tahun', date('Y'));
        $leaveTypeId = $request->input('leave_type_id');

        $empQuery = Employee::with(['department', 'branch', 'position'])
            ->where('status', 'active');

        if ($branchId = $request->input('kode_cabang')) {
            $empQuery->where('branch_id', $branchId);
        }

        if ($deptId = $request->input('kode_dept')) {
            $empQuery->where('department_id', $deptId);
        }

        $employees = $empQuery->orderBy('first_name')->get();

        // Leave Balances for year
        $balanceQuery = LeaveBalance::where('year', $tahun);
        if ($leaveTypeId) {
            $balanceQuery->where('leave_type_id', $leaveTypeId);
        }
        $balances = $balanceQuery->get()->groupBy('employee_id');

        // Approved Leave Requests in that year
        $leaveReqQuery = LeaveRequest::where('status', 'approved')
            ->whereYear('start_date', $tahun);
        if ($leaveTypeId) {
            $leaveReqQuery->where('leave_type_id', $leaveTypeId);
        }
        $approvedRequests = $leaveReqQuery->get()->groupBy('employee_id');

        // Build Leave Ledger Matrix per employee
        $reportData = [];
        $totalInitialQuota = 0;
        $totalUsedQuota = 0;
        $totalRemainingQuota = 0;

        foreach ($employees as $emp) {
            $empBalances = $balances->get($emp->id) ?? collect();
            $initial = $empBalances->sum('initial_quota');
            $used = $empBalances->sum('used_quota');
            $remaining = $empBalances->sum('remaining_quota');

            // Fallback default if balance record doesn't exist yet
            if ($initial == 0) {
                $initial = 12; // default standard annual leave
                $remaining = max(0, $initial - $used);
            }

            // Calculate monthly breakdown (Jan - Dec)
            $empLeaves = $approvedRequests->get($emp->id) ?? collect();
            $months = array_fill(1, 12, 0);
            foreach ($empLeaves as $req) {
                $m = (int)$req->start_date->format('m');
                $months[$m] += $req->total_days;
            }

            $actualTaken = array_sum($months);
            if ($used == 0 && $actualTaken > 0) {
                $used = $actualTaken;
                $remaining = max(0, $initial - $used);
            }

            $totalInitialQuota += $initial;
            $totalUsedQuota += $used;
            $totalRemainingQuota += $remaining;

            $reportData[] = [
                'employee' => $emp,
                'initial_quota' => $initial,
                'months' => $months,
                'total_taken' => $used,
                'remaining' => $remaining,
            ];
        }

        $companyName = SystemSetting::get('company_name', 'PT Solusi Teknologi Nusantara');
        $companyAddress = SystemSetting::get('company_address', 'Kantor Pusat');
        $companyPhone = SystemSetting::get('company_phone', '');
        $printedAt = Carbon::now()->locale(app()->getLocale())->translatedFormat('d F Y H:i');
        $periodLabel = __('Tahun Periode: :year', ['year' => $tahun]);
        $signers = $this->getSigners($request);

        $selectedBranch = $request->kode_cabang ? Branch::find($request->kode_cabang)?->name : 'Semua Cabang';
        $selectedDept = $request->kode_dept ? Department::find($request->kode_dept)?->name : 'Semua Departemen';
        $selectedLeaveType = $leaveTypeId ? LeaveType::find($leaveTypeId)?->name : 'Semua Jenis Cuti';

        $filename = "Laporan_Cuti_" . $tahun . '_' . date('His');

        if ($action === 'excel' || $request->has('exportButton')) {
            return ReportXlsxService::downloadLeave(
                $reportData, $companyName, $periodLabel, $selectedBranch, $selectedDept, $selectedLeaveType,
                $printedAt, $signers, $totalInitialQuota, $totalUsedQuota, $totalRemainingQuota, $filename . '.xlsx'
            );
        }

        $pdf = ArabicPdfHelper::loadView('reports.pdf.leave', compact(
            'reportData', 'tahun', 'periodLabel', 'companyName', 'companyAddress', 'companyPhone',
            'selectedBranch', 'selectedDept', 'selectedLeaveType', 'printedAt', 'signers',
            'totalInitialQuota', 'totalUsedQuota', 'totalRemainingQuota'
        ), 'a4', 'landscape');

        return $pdf->download($filename . '.pdf');
    }

    // ==========================================
    // 5. HELPER: EMPLOYEES BY FILTER (AJAX)
    // ==========================================
    public function getEmployeesByFilter(Request $request)
    {
        $query = Employee::where('status', 'active');

        if ($branchId = $request->input('branch_id')) {
            $query->where('branch_id', $branchId);
        }

        if ($deptId = $request->input('department_id')) {
            $query->where('department_id', $deptId);
        }

        $employees = $query->orderBy('first_name')
            ->select('id', 'employee_code', 'first_name', 'last_name')
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'name' => $e->full_name . ' (' . $e->employee_code . ')',
            ]);

        return response()->json($employees);
    }

    // ==========================================
    // 5. LAPORAN KLAIM REIMBURSEMENT
    // ==========================================
    public function reimbursement(Request $request)
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();
        $categories = ReimbursementCategory::where('is_active', true)->orderBy('name')->get();

        $currentMonth = date('m');
        $currentYear = date('Y');
        $startYear = 2022;

        return view('reports.reimbursement', compact(
            'branches',
            'departments',
            'employees',
            'categories',
            'currentMonth',
            'currentYear',
            'startYear'
        ));
    }

    public function exportReimbursement(Request $request)
    {
        $action = $request->input('action_type', 'pdf');
        $mode = $request->input('periode_laporan', '1'); // 1: cut-off, 2: monthly, 3: custom date range
        $bulan = (int)$request->input('bulan', date('m'));
        $tahun = (int)$request->input('tahun', date('Y'));

        if ($mode == '1') {
            $cutOffDay = (int)SystemSetting::get('payroll_cutoff_day', 25);
            $startDate = Carbon::create($tahun, $bulan, $cutOffDay + 1)->subMonth()->format('Y-m-d');
            $endDate = Carbon::create($tahun, $bulan, $cutOffDay)->format('Y-m-d');
            $periodLabel = __('Periode Cut-Off: :start - :end', [
                'start' => Carbon::parse($startDate)->format('d/m/Y'),
                'end' => Carbon::parse($endDate)->format('d/m/Y'),
            ]);
        } elseif ($mode == '2') {
            $startDate = Carbon::create($tahun, $bulan, 1)->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');
            $periodLabel = __('Bulan Berjalan: :month', [
                'month' => Carbon::create($tahun, $bulan, 1)->locale(app()->getLocale())->translatedFormat('F Y'),
            ]);
        } else {
            $startDate = $request->input('dari', date('Y-m-01'));
            $endDate = $request->input('sampai', date('Y-m-d'));
            $periodLabel = __('Rentang: :start - :end', [
                'start' => Carbon::parse($startDate)->format('d/m/Y'),
                'end' => Carbon::parse($endDate)->format('d/m/Y'),
            ]);
        }

        $query = ReimbursementRequest::with(['employee.department', 'employee.branch', 'branch', 'category', 'approver', 'payer'])
            ->whereBetween('claim_date', [$startDate, $endDate]);

        if ($branchId = $request->input('kode_cabang')) {
            $query->where('branch_id', $branchId);
        }
        if ($deptId = $request->input('kode_dept')) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $deptId));
        }
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($currency = $request->input('currency')) {
            $query->where('currency', $currency);
        }
        if ($nik = $request->input('nik')) {
            $query->where('employee_id', $nik);
        }

        $items = $query->orderBy('claim_date', 'asc')->orderBy('id', 'asc')->get();

        $totalAmountIdr = 0;
        $totalApprovedIdr = 0;
        $totalAmountSar = 0;
        $totalApprovedSar = 0;

        foreach ($items as $item) {
            $c = $item->currency ?? 'IDR';
            if ($c === 'SAR') {
                $totalAmountSar += (float)$item->amount;
                $totalApprovedSar += (float)($item->approved_amount ?? $item->amount);
            } else {
                $totalAmountIdr += (float)$item->amount;
                $totalApprovedIdr += (float)($item->approved_amount ?? $item->amount);
            }
        }

        $branch = $request->input('kode_cabang') ? (Branch::find($request->input('kode_cabang'))?->name ?? 'Semua Cabang') : 'Semua Cabang';
        $dept = $request->input('kode_dept') ? (Department::find($request->input('kode_dept'))?->name ?? 'Semua Departemen') : 'Semua Departemen';
        $category = $request->input('category_id') ? (ReimbursementCategory::find($request->input('category_id'))?->name ?? 'Semua Kategori') : 'Semua Kategori';
        $statusText = $request->input('status') ? match($request->input('status')) {
            'pending' => __('Menunggu Approval'),
            'approved' => __('Disetujui'),
            'paid' => __('Selesai Dibayar (Lunas)'),
            'rejected' => __('Ditolak'),
            default => ucfirst($request->input('status')),
        } : __('Semua Status');

        $signers = $this->getSigners($request);
        $companyName = SystemSetting::get('company_name', 'PT Layang Digital Innovation');
        $companyAddress = SystemSetting::get('company_address', 'Kota Tasikmalaya, Jawa Barat, Indonesia');
        $companyPhone = SystemSetting::get('company_phone', '082116925851');
        $printedAt = now()->locale(app()->getLocale())->translatedFormat('d F Y H:i');

        $filename = 'Laporan_Reimbursement_' . date('Ymd_His');

        if ($action === 'excel') {
            return ReportXlsxService::downloadReimbursement(
                $items,
                $companyName,
                $periodLabel,
                $branch,
                $dept,
                $category,
                $statusText,
                $printedAt,
                $signers,
                $totalAmountIdr,
                $totalApprovedIdr,
                $totalAmountSar,
                $totalApprovedSar,
                $filename . '.xlsx'
            );
        }

        $pdf = ArabicPdfHelper::loadView('reports.pdf.reimbursements_summary', compact(
            'items',
            'companyName',
            'companyAddress',
            'companyPhone',
            'periodLabel',
            'branch',
            'dept',
            'category',
            'statusText',
            'signers',
            'printedAt',
            'totalAmountIdr',
            'totalApprovedIdr',
            'totalAmountSar',
            'totalApprovedSar'
        ), 'a4', 'landscape');

        return $pdf->download($filename . '.pdf');
    }

    // ==========================================
    // 6. LAPORAN PINJAMAN KARYAWAN
    // ==========================================
    public function loans(Request $request)
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();

        $currentMonth = date('m');
        $currentYear = date('Y');
        $startYear = 2022;

        return view('reports.loans', compact(
            'branches',
            'departments',
            'employees',
            'currentMonth',
            'currentYear',
            'startYear'
        ));
    }

    public function exportLoans(Request $request)
    {
        $action = $request->input('action_type', 'pdf');
        $mode = $request->input('periode_laporan', '1');
        $bulan = (int)$request->input('bulan', date('m'));
        $tahun = (int)$request->input('tahun', date('Y'));

        if ($mode == '1') {
            $cutOffDay = (int)SystemSetting::get('payroll_cutoff_day', 25);
            $startDate = Carbon::create($tahun, $bulan, $cutOffDay + 1)->subMonth()->format('Y-m-d');
            $endDate = Carbon::create($tahun, $bulan, $cutOffDay)->format('Y-m-d');
            $periodLabel = __('Periode Cut-Off: :start - :end', [
                'start' => Carbon::parse($startDate)->format('d/m/Y'),
                'end' => Carbon::parse($endDate)->format('d/m/Y'),
            ]);
        } elseif ($mode == '2') {
            $startDate = Carbon::create($tahun, $bulan, 1)->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');
            $periodLabel = __('Bulan Berjalan: :month', [
                'month' => Carbon::create($tahun, $bulan, 1)->locale(app()->getLocale())->translatedFormat('F Y'),
            ]);
        } else {
            $startDate = $request->input('dari', date('Y-m-01'));
            $endDate = $request->input('sampai', date('Y-m-d'));
            $periodLabel = __('Rentang: :start - :end', [
                'start' => Carbon::parse($startDate)->format('d/m/Y'),
                'end' => Carbon::parse($endDate)->format('d/m/Y'),
            ]);
        }

        $query = EmployeeLoan::with(['employee.department', 'employee.branch', 'branch', 'approver', 'disburser', 'installments'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        if ($branchId = $request->input('kode_cabang')) {
            $query->where('branch_id', $branchId);
        }
        if ($deptId = $request->input('kode_dept')) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $deptId));
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($currency = $request->input('currency')) {
            $query->where('currency', $currency);
        }
        if ($nik = $request->input('nik')) {
            $query->where('employee_id', $nik);
        }

        $loans = $query->orderBy('created_at', 'asc')->orderBy('id', 'asc')->get();

        $totalPrincipalIdr = 0;
        $totalRepaidIdr = 0;
        $totalRemainingIdr = 0;
        $totalPrincipalSar = 0;
        $totalRepaidSar = 0;
        $totalRemainingSar = 0;

        foreach ($loans as $loan) {
            $c = $loan->currency ?? 'IDR';
            $principal = (float)$loan->effective_principal;
            $repaid = (float)$loan->total_repaid_amount;
            $remaining = (float)$loan->remaining_amount;

            if ($c === 'SAR') {
                $totalPrincipalSar += $principal;
                $totalRepaidSar += $repaid;
                $totalRemainingSar += $remaining;
            } else {
                $totalPrincipalIdr += $principal;
                $totalRepaidIdr += $repaid;
                $totalRemainingIdr += $remaining;
            }
        }

        $branch = $request->input('kode_cabang') ? (Branch::find($request->input('kode_cabang'))?->name ?? 'Semua Cabang') : 'Semua Cabang';
        $dept = $request->input('kode_dept') ? (Department::find($request->input('kode_dept'))?->name ?? 'Semua Departemen') : 'Semua Departemen';
        $statusText = $request->input('status') ? match($request->input('status')) {
            'pending' => __('Menunggu Approval'),
            'approved' => __('Disetujui (Siap Cair)'),
            'active', 'disbursed' => __('Aktif Berjalan'),
            'completed' => __('Lunas'),
            'rejected' => __('Ditolak'),
            'cancelled' => __('Dibatalkan'),
            default => ucfirst($request->input('status')),
        } : __('Semua Status');

        $signers = $this->getSigners($request);
        $companyName = SystemSetting::get('company_name', 'PT Layang Digital Innovation');
        $companyAddress = SystemSetting::get('company_address', 'Kota Tasikmalaya, Jawa Barat, Indonesia');
        $companyPhone = SystemSetting::get('company_phone', '082116925851');
        $printedAt = now()->locale(app()->getLocale())->translatedFormat('d F Y H:i');

        $filename = 'Laporan_Pinjaman_' . date('Ymd_His');

        if ($action === 'excel') {
            return ReportXlsxService::downloadLoans(
                $loans,
                $companyName,
                $periodLabel,
                $branch,
                $dept,
                $statusText,
                $printedAt,
                $signers,
                $totalPrincipalIdr,
                $totalRepaidIdr,
                $totalRemainingIdr,
                $totalPrincipalSar,
                $totalRepaidSar,
                $totalRemainingSar,
                $filename . '.xlsx'
            );
        }

        $pdf = ArabicPdfHelper::loadView('reports.pdf.loans_summary', compact(
            'loans',
            'companyName',
            'companyAddress',
            'companyPhone',
            'periodLabel',
            'branch',
            'dept',
            'statusText',
            'signers',
            'printedAt',
            'totalPrincipalIdr',
            'totalRepaidIdr',
            'totalRemainingIdr',
            'totalPrincipalSar',
            'totalRepaidSar',
            'totalRemainingSar'
        ), 'a4', 'landscape');

        return $pdf->download($filename . '.pdf');
    }

    // ==========================================
    // 7. HELPER: DYNAMIC REPORT SIGNERS
    // ==========================================
    protected function getSigners(Request $request): array
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $employee = $user?->employee;

        // 1. Dibuat Oleh (Penyusun Laporan)
        $makerName = $employee ? $employee->full_name : ($user->name ?? 'Administrator');
        $makerTitle = $employee?->position?->title 
            ?? ($user?->role === 'super_admin' ? 'Super Administrator' : ($user?->role === 'hr_admin' ? 'HR Administrator' : 'Staff HR & Presensi'));

        // 2. Mengetahui & Menyetujui (Pejabat Berwenang)
        $customApproverName = SystemSetting::get('report_approver_name');
        $customApproverTitle = SystemSetting::get('report_approver_title');

        if ($customApproverName && $customApproverTitle) {
            $approverName = $customApproverName;
            $approverTitle = $customApproverTitle;
        } else {
            $deptId = $request->input('kode_dept');
            $dept = $deptId ? Department::with('manager.position')->find($deptId) : null;

            if ($dept && $dept->manager) {
                $approverName = $dept->manager->full_name;
                $approverTitle = $dept->manager->position?->title ?? ("Manager " . $dept->name);
            } else {
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

    protected function cleanShiftCode(?string $rawCode): string
    {
        $clean = strtoupper(trim(str_replace(['SH-', 'SHIFT-'], '', $rawCode ?? '-')));
        if ($clean === 'OFF' || $clean === '-' || $clean === '') {
            return $clean ?: '-';
        }
        if ($clean === 'REGULER') return 'REG';
        if ($clean === 'SIANG') return 'SIA';
        if ($clean === 'PAGI') return 'PAG';
        if ($clean === 'MALAM') return 'MLM';
        return strlen($clean) > 4 ? substr($clean, 0, 3) : $clean;
    }
}

