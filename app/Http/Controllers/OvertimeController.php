<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\OvertimeRate;
use App\Models\OvertimeRequest;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OvertimeController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();

        $query = OvertimeRequest::with(['employee.department', 'approver', 'overtimeRate']);

        // Scope by Role
        $empQuery = Employee::where('status', 'active');
        $statsQuery = OvertimeRequest::query();

        if ($currentUser && $currentUser->isEmployee()) {
            $employeeId = $currentUser->employee?->id ?? 0;
            $query->where('employee_id', $employeeId);
            $empQuery->where('id', $employeeId);
            $statsQuery->where('employee_id', $employeeId);
        } elseif ($currentUser && $currentUser->role === 'manager' && $currentUser->employee?->department_id) {
            $deptId = $currentUser->employee->department_id;
            $query->whereHas('employee', fn($q) => $q->where('department_id', $deptId));
            $empQuery->where('department_id', $deptId);
            $statsQuery->whereHas('employee', fn($q) => $q->where('department_id', $deptId));
        }

        // Search Filter for Overtime Requests
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($eq) use ($search) {
                      $eq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('employee_code', 'like', "%{$search}%")
                         ->orWhereHas('department', function ($dq) use ($search) {
                             $dq->where('name', 'like', "%{$search}%");
                         });
                  });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($empId = $request->input('employee_id')) {
            $query->where('employee_id', $empId);
        }

        if ($month = $request->input('month')) {
            $query->where('date', 'like', "{$month}%");
        }

        $requests = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends(array_merge($request->except('page'), ['tab' => 'requests']));
        $employees = $empQuery->orderBy('first_name')->get();

        // Statistics
        $totalHoursApproved = (clone $statsQuery)->where('status', 'approved')->sum('actual_hours');
        $pendingCount = (clone $statsQuery)->where('status', 'pending')->count();
        $overtimeRates = OvertimeRate::orderBy('id', 'asc')->get();

        return view('overtime.index', compact('requests', 'employees', 'totalHoursApproved', 'pendingCount', 'overtimeRates'));
    }

    public function create()
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();
        $employees = collect();

        $employees = $empQuery ?? Employee::where('status', 'active')->orderBy('first_name')->get();
        if ($currentUser && $currentUser->isEmployee()) {
            if (!$currentUser->employee) {
                return redirect()->route('overtime.index')->with('error', 'Akun Anda belum terhubung dengan profil karyawan.');
            }
            $employees = collect();
        } elseif ($currentUser && $currentUser->role === 'manager' && $currentUser->employee?->department_id) {
            $employees = Employee::where('status', 'active')
                ->where('department_id', $currentUser->employee->department_id)
                ->orderBy('first_name')
                ->get();
        } else {
            $employees = Employee::where('status', 'active')
                ->orderBy('first_name')
                ->get();
        }

        $activeRates = OvertimeRate::active()->orderBy('id')->get();

        return view('overtime.create', compact('employees', 'activeRates'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'overtime_rate_id' => 'nullable|exists:overtime_rates,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'reason' => 'required|string|max:500',
        ]);

        // Regular employee can only submit overtime for themselves
        if ($currentUser && $currentUser->isEmployee()) {
            if (!$currentUser->employee) {
                abort(403, 'Akun Anda belum terhubung dengan profil karyawan.');
            }
            $validated['employee_id'] = $currentUser->employee->id;
        }

        $start = Carbon::parse("{$validated['date']} {$validated['start_time']}");
        $end = Carbon::parse("{$validated['date']} {$validated['end_time']}");

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay(); // overnight overtime
        }

        $estimatedHours = round($start->diffInMinutes($end) / 60, 2);

        $count = OvertimeRequest::count() + 1;
        $reqNumber = 'OT-' . date('Ym') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $ot = OvertimeRequest::create([
            'request_number' => $reqNumber,
            'employee_id' => $validated['employee_id'],
            'overtime_rate_id' => $validated['overtime_rate_id'] ?? null,
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'estimated_hours' => $estimatedHours,
            'actual_hours' => $estimatedHours,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'create_overtime',
            'description' => "Pengajuan lembur ({$reqNumber}) untuk {$ot->employee->full_name} ({$estimatedHours} jam).",
        ]);

        return redirect()->route('overtime.index')->with('success', "Pengajuan lembur ({$reqNumber}) berhasil diajukan.");
    }

    public function approve(Request $request, OvertimeRequest $overtimeRequest)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();

        // Strict authorization: only manager, hr_admin, or super_admin
        if (!$currentUser || !$currentUser->canApprove()) {
            abort(403, 'Akses ditolak. Hanya Manager, HR Admin, atau Super Admin yang berhak menyetujui atau menolak lembur (SPKL).');
        }

        // Manager can only approve employees in their own department
        if ($currentUser->role === 'manager' && $currentUser->employee) {
            if ($overtimeRequest->employee->department_id !== $currentUser->employee->department_id) {
                abort(403, 'Akses ditolak. Anda hanya berwenang menyetujui pengajuan lembur karyawan di departemen Anda.');
            }
        }

        $action = $request->input('action'); // 'approve' or 'reject'
        $status = $action === 'reject' ? 'rejected' : 'approved';

        $overtimeRequest->update([
            'status' => $status,
            'approved_by' => $currentUser->id,
            'approved_at' => now(),
            'actual_hours' => $request->input('actual_hours', $overtimeRequest->estimated_hours),
        ]);

        AuditLog::create([
            'user_id' => $currentUser->id,
            'event' => 'approve_overtime',
            'auditable_type' => OvertimeRequest::class,
            'auditable_id' => $overtimeRequest->id,
            'description' => "Pengajuan lembur {$overtimeRequest->request_number} ({$overtimeRequest->employee->full_name}) di-{$status} oleh {$currentUser->name} (" . str_replace('_', ' ', $currentUser->role) . ").",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Pengajuan lembur {$overtimeRequest->request_number} telah di-" . ($status === 'approved' ? 'setujui' : 'tolak') . ".");
    }

    public function updateSettings(Request $request)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->isHrAdmin()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas mengubah kebijakan lembur.');
        }

        $validated = $request->validate([
            'overtime_calculation_type' => 'required|in:flat,depnaker,saudi_article_107,auto_country',
            'overtime_rate_per_hour' => 'required|numeric|min:0',
            'overtime_rate_weekend_per_hour' => 'required|numeric|min:0',
            'overtime_depnaker_divisor' => 'nullable|numeric|min:1',
            'overtime_saudi_daily_hours' => 'nullable|numeric|between:4,12',
            'overtime_saudi_month_days' => 'nullable|numeric|between:20,31',
            'overtime_saudi_is_ramadan' => 'nullable|boolean',
        ]);

        SystemSetting::set('overtime_calculation_type', $validated['overtime_calculation_type'], 'overtime', 'Metode Perhitungan Lembur');
        SystemSetting::set('overtime_rate_per_hour', (string) $validated['overtime_rate_per_hour'], 'overtime', 'Rate Lembur Standar (Rp/Jam)');
        SystemSetting::set('overtime_rate_weekend_per_hour', (string) $validated['overtime_rate_weekend_per_hour'], 'overtime', 'Rate Lembur Weekend/Libur (Rp/Jam)');
        if (!empty($validated['overtime_depnaker_divisor'])) {
            SystemSetting::set('overtime_depnaker_divisor', (string) $validated['overtime_depnaker_divisor'], 'overtime', 'Pembagi Formula Depnaker');
        }
        if (!empty($validated['overtime_saudi_daily_hours'])) {
            SystemSetting::set('overtime_saudi_daily_hours', (string) $validated['overtime_saudi_daily_hours'], 'overtime', 'Jam Kerja Normal Harian Arab Saudi');
        }
        if (!empty($validated['overtime_saudi_month_days'])) {
            SystemSetting::set('overtime_saudi_month_days', (string) $validated['overtime_saudi_month_days'], 'overtime', 'Hari Kerja Pembagi Sebulan Arab Saudi');
        }
        SystemSetting::set('overtime_saudi_is_ramadan', $request->boolean('overtime_saudi_is_ramadan') ? '1' : '0', 'overtime', 'Status Bulan Ramadan Arab Saudi (Jam Normal 6 Jam/Hari)');

        // Also sync the standard OvertimeRate rows
        OvertimeRate::where('code', 'OT_WEEKDAY')->update([
            'rate_per_hour' => $validated['overtime_rate_per_hour'],
            'calculation_type' => $validated['overtime_calculation_type'] === 'depnaker' ? 'depnaker_formula' : ($validated['overtime_calculation_type'] === 'saudi_article_107' ? 'saudi_article_107' : 'flat'),
        ]);
        OvertimeRate::where('code', 'OT_WEEKEND')->update([
            'rate_per_hour' => $validated['overtime_rate_weekend_per_hour'],
        ]);

        AuditLog::create([
            'user_id' => $currentUser->id,
            'event' => 'update_overtime_policy',
            'description' => "Memperbarui kebijakan rate lembur standar perusahaan (Tipe: {$validated['overtime_calculation_type']}, Hari Kerja: Rp " . number_format($validated['overtime_rate_per_hour'], 0, ',', '.') . "/jam, Weekend: Rp " . number_format($validated['overtime_rate_weekend_per_hour'], 0, ',', '.') . "/jam).",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Kebijakan dan tarif lembur perusahaan berhasil disimpan!');
    }

    public function createRate()
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->isHrAdmin()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas menambah rate lembur.');
        }

        return view('overtime.rates.create');
    }

    public function storeRate(Request $request)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->isHrAdmin()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas menambah rate lembur.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:overtime_rates,code',
            'day_type' => 'required|in:weekday,weekend,holiday,all',
            'calculation_type' => 'required|in:flat,depnaker_formula,percentage,saudi_article_107',
            'currency' => 'nullable|in:IDR,SAR',
            'rate_per_hour' => 'required|numeric|min:0',
            'multiplier' => 'required|numeric|min:0',
            'is_default' => 'nullable|boolean',
            'description' => 'nullable|string|max:255',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $makeDefault = $request->boolean('is_default', false);
        if ($makeDefault) {
            OvertimeRate::query()->update(['is_default' => false]);
            $validated['is_default'] = true;
        } else {
            $validated['is_default'] = false;
        }
        $validated['currency'] = $validated['currency'] ?? (($validated['calculation_type'] === 'saudi_article_107') ? 'SAR' : 'IDR');

        $rate = OvertimeRate::create($validated);

        AuditLog::create([
            'user_id' => $currentUser->id,
            'event' => 'create_overtime_rate',
            'description' => "Menambahkan master rate lembur baru: '{$rate->name}' ({$rate->code}).",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('overtime.index', ['tab' => 'rates'])->with('success', "Master rate lembur '{$rate->name}' berhasil ditambahkan!");
    }

    public function editRate(OvertimeRate $overtimeRate)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->isHrAdmin()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas mengubah rate lembur.');
        }

        return view('overtime.rates.edit', compact('overtimeRate'));
    }

    public function updateRate(Request $request, OvertimeRate $overtimeRate)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->isHrAdmin()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas mengubah rate lembur.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'day_type' => 'required|in:weekday,weekend,holiday,all',
            'calculation_type' => 'required|in:flat,depnaker_formula,percentage,saudi_article_107',
            'currency' => 'nullable|in:IDR,SAR',
            'rate_per_hour' => 'required|numeric|min:0',
            'multiplier' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
            'is_default' => 'nullable|boolean',
            'description' => 'nullable|string|max:255',
        ]);

        $validated['currency'] = $validated['currency'] ?? (($validated['calculation_type'] === 'saudi_article_107') ? 'SAR' : 'IDR');
        $isActive = (bool) $validated['is_active'];
        $makeDefault = $request->boolean('is_default', false);

        if ($makeDefault && $isActive) {
            OvertimeRate::query()->where('id', '!=', $overtimeRate->id)->update(['is_default' => false]);
            $validated['is_default'] = true;
        } elseif (!$isActive && $overtimeRate->is_default) {
            $validated['is_default'] = false;
            // Transfer default to another active rate if available
            OvertimeRate::where('id', '!=', $overtimeRate->id)->where('is_active', true)->first()?->update(['is_default' => true]);
        }

        $overtimeRate->update($validated);

        AuditLog::create([
            'user_id' => $currentUser->id,
            'event' => 'update_overtime_rate',
            'description' => "Memperbarui master rate lembur: '{$overtimeRate->name}' ({$overtimeRate->code}).",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('overtime.index', ['tab' => 'rates'])->with('success', "Master rate lembur '{$overtimeRate->name}' berhasil diperbarui!");
    }

    public function setDefaultRate(OvertimeRate $overtimeRate)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->isHrAdmin()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas mengubah rate lembur.');
        }

        if (!$overtimeRate->is_active) {
            return back()->with('error', 'Rate lembur yang nonaktif tidak dapat dijadikan sebagai rate default. Aktifkan rate terlebih dahulu.');
        }

        OvertimeRate::query()->update(['is_default' => false]);
        $overtimeRate->update(['is_default' => true]);

        // Sync with system setting if appropriate
        if ($overtimeRate->calculation_type === 'saudi_article_107') {
            SystemSetting::set('overtime_calculation_type', 'saudi_article_107', 'overtime', 'Metode Perhitungan Lembur');
        } elseif ($overtimeRate->calculation_type === 'depnaker_formula') {
            SystemSetting::set('overtime_calculation_type', 'depnaker', 'overtime', 'Metode Perhitungan Lembur');
        } elseif ($overtimeRate->calculation_type === 'flat') {
            SystemSetting::set('overtime_calculation_type', 'flat', 'overtime', 'Metode Perhitungan Lembur');
            SystemSetting::set('overtime_rate_per_hour', (string) $overtimeRate->rate_per_hour, 'overtime', 'Rate Lembur Standar (Rp/Jam)');
        }

        AuditLog::create([
            'user_id' => $currentUser->id,
            'event' => 'set_default_overtime_rate',
            'description' => "Menetapkan master rate lembur '{$overtimeRate->name}' ({$overtimeRate->code}) sebagai rate default perusahaan.",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', "Master rate '{$overtimeRate->name}' berhasil dijadikan sebagai rate lembur default perusahaan!");
    }

    public function destroyRate(OvertimeRate $overtimeRate)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->isHrAdmin()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas menghapus rate lembur.');
        }

        if ($overtimeRate->is_default) {
            return back()->with('error', 'Master rate lembur standar default tidak dapat dihapus.');
        }

        $name = $overtimeRate->name;
        $overtimeRate->delete();

        AuditLog::create([
            'user_id' => $currentUser->id,
            'event' => 'delete_overtime_rate',
            'description' => "Menghapus master rate lembur '{$name}'.",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', "Master rate lembur '{$name}' telah dihapus.");
    }
}
