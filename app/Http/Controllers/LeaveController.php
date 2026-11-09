<?php

namespace App\Http\Controllers;

use App\Models\AttendanceDailySummary;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestDay;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        $query = LeaveRequest::with(['employee.department', 'leaveType', 'approver']);

        // Scope by Role
        if ($currentUser && $currentUser->isEmployee()) {
            $query->where('employee_id', $currentUser->employee?->id ?? 0);
        } elseif ($currentUser && $currentUser->role === 'manager' && $currentUser->employee?->department_id) {
            $deptId = $currentUser->employee->department_id;
            $query->whereHas('employee', fn($q) => $q->where('department_id', $deptId));
        }

        // Search Filter for Requests
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
                  })
                  ->orWhereHas('leaveType', function ($tq) use ($search) {
                      $tq->where('name', 'like', "%{$search}%")
                         ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($typeId = $request->input('leave_type_id')) {
            $query->where('leave_type_id', $typeId);
        }

        $requests = $query->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'req_page')
            ->appends(array_merge($request->except('req_page'), ['tab' => 'requests']));

        $leaveTypes = LeaveType::where('is_active', true)->get();
        $allLeaveTypes = LeaveType::withCount('requests')->orderBy('id')->get();

        // Scope employees dropdown and balances
        $empQuery = Employee::where('status', 'active');
        $balQuery = LeaveBalance::with(['employee', 'leaveType'])->where('year', date('Y'));

        if ($currentUser && $currentUser->isEmployee()) {
            $empQuery->where('id', $currentUser->employee?->id ?? 0);
            $balQuery->where('employee_id', $currentUser->employee?->id ?? 0);
        } elseif ($currentUser && $currentUser->role === 'manager' && $currentUser->employee?->department_id) {
            $empQuery->where('department_id', $currentUser->employee->department_id);
            $balQuery->whereHas('employee', fn($q) => $q->where('department_id', $currentUser->employee->department_id));
        }

        $employees = $empQuery->get();
        $balances = $balQuery->limit(15)->get();

        // All Balances with Query and Pagination
        $allBalancesQuery = LeaveBalance::with(['employee.department', 'leaveType'])
            ->where('year', date('Y'))
            ->orderBy('employee_id');

        if ($currentUser && $currentUser->isEmployee()) {
            $allBalancesQuery->where('employee_id', $currentUser->employee?->id ?? 0);
        } elseif ($currentUser && $currentUser->role === 'manager' && $currentUser->employee?->department_id) {
            $allBalancesQuery->whereHas('employee', fn($q) => $q->where('department_id', $currentUser->employee->department_id));
        }

        if ($balSearch = $request->input('bal_search')) {
            $allBalancesQuery->where(function ($q) use ($balSearch) {
                $q->whereHas('employee', function ($eq) use ($balSearch) {
                    $eq->where('first_name', 'like', "%{$balSearch}%")
                       ->orWhere('last_name', 'like', "%{$balSearch}%")
                       ->orWhere('employee_code', 'like', "%{$balSearch}%")
                       ->orWhereHas('department', function ($dq) use ($balSearch) {
                           $dq->where('name', 'like', "%{$balSearch}%");
                       });
                })->orWhereHas('leaveType', function ($tq) use ($balSearch) {
                    $tq->where('name', 'like', "%{$balSearch}%")
                       ->orWhere('code', 'like', "%{$balSearch}%");
                });
            });
        }

        $allBalances = $allBalancesQuery->paginate(15, ['*'], 'bal_page')
            ->appends(array_merge($request->except('bal_page'), ['tab' => 'balances']));

        return view('leaves.index', compact('requests', 'leaveTypes', 'allLeaveTypes', 'employees', 'balances', 'allBalances'));
    }

    public function create()
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        $empQuery = Employee::where('status', 'active');
        if ($currentUser && $currentUser->isEmployee()) {
            $empQuery->where('id', $currentUser->employee?->id ?? 0);
        } elseif ($currentUser && $currentUser->role === 'manager' && $currentUser->employee?->department_id) {
            $empQuery->where('department_id', $currentUser->employee->department_id);
        }

        $employees = $empQuery->orderBy('first_name')->orderBy('last_name')->get();
        $leaveTypes = LeaveType::where('is_active', true)->get();

        return view('leaves.create', compact('employees', 'leaveTypes'));
    }

    public function store(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ]);

        // Regular employee can only submit leave for themselves
        if ($currentUser && $currentUser->isEmployee()) {
            if (!$currentUser->employee) {
                abort(403, 'Akun Anda belum terhubung dengan profil karyawan.');
            }
            $validated['employee_id'] = $currentUser->employee->id;
        }

        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);
        $period = CarbonPeriod::create($start, $end);

        // Count workdays (exclude weekends)
        $workdays = 0;
        $dates = [];
        foreach ($period as $date) {
            if (!$date->isWeekend()) {
                $workdays++;
                $dates[] = $date->format('Y-m-d');
            }
        }

        if ($workdays === 0) {
            return back()->with('error', 'Rentang tanggal cuti hanya mencakup hari libur akhir pekan.');
        }

        // Generate request number
        $count = LeaveRequest::count() + 1;
        $reqNum = 'LV-' . date('Ym') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $leaveReq = LeaveRequest::create([
                'request_number' => $reqNum,
                'employee_id' => $validated['employee_id'],
                'leave_type_id' => $validated['leave_type_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'total_days' => $workdays,
                'reason' => $validated['reason'],
                'status' => 'submitted',
            ]);

            foreach ($dates as $d) {
                LeaveRequestDay::create([
                    'leave_request_id' => $leaveReq->id,
                    'date' => $d,
                ]);
            }

            DB::commit();

            return redirect()->route('leaves.index', ['tab' => 'requests'])->with('success', "Pengajuan cuti ({$reqNum}) untuk {$workdays} hari kerja berhasil diajukan.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengajukan cuti: ' . $e->getMessage());
        }
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        // Strict authorization: only manager, hr_admin, or super_admin can approve/reject
        if (!$currentUser || !$currentUser->canApprove()) {
            abort(403, 'Akses ditolak. Hanya Manager, HR Admin, atau Super Admin yang berhak menyetujui atau menolak pengajuan cuti & izin.');
        }

        // Manager can only approve employees in their own department
        if ($currentUser->role === 'manager' && $currentUser->employee) {
            if ($leaveRequest->employee->department_id !== $currentUser->employee->department_id) {
                abort(403, 'Akses ditolak. Anda hanya berwenang menyetujui pengajuan cuti karyawan di departemen Anda.');
            }
        }

        $action = $request->input('action'); // 'approve' or 'reject'
        $newStatus = $action === 'reject' ? 'rejected' : 'approved';

        DB::beginTransaction();
        try {
            $leaveRequest->update([
                'status' => $newStatus,
                'approved_by' => $currentUser->id,
                'approved_at' => now(),
            ]);

            if ($newStatus === 'approved') {
                // Deduct balance
                $initialQuota = $leaveRequest->leaveType?->default_quota ?? 12;
                $balance = LeaveBalance::firstOrCreate(
                    [
                        'employee_id' => $leaveRequest->employee_id,
                        'leave_type_id' => $leaveRequest->leave_type_id,
                        'year' => Carbon::parse($leaveRequest->start_date)->year,
                    ],
                    [
                        'initial_quota' => $initialQuota,
                        'used_quota' => 0,
                        'remaining_quota' => $initialQuota,
                    ]
                );

                $balance->increment('used_quota', $leaveRequest->total_days);
                $balance->decrement('remaining_quota', $leaveRequest->total_days);

                // Update attendance daily summaries for each day
                $days = $leaveRequest->days;
                foreach ($days as $day) {
                    AttendanceDailySummary::updateOrCreate(
                        [
                            'employee_id' => $leaveRequest->employee_id,
                            'date' => $day->date,
                        ],
                        [
                            'status' => 'on_leave',
                            'notes' => 'Cuti Disetujui: ' . $leaveRequest->leaveType->name,
                        ]
                    );
                }
            }

            AuditLog::create([
                'user_id' => $currentUser->id,
                'event' => 'approve_leave',
                'auditable_type' => LeaveRequest::class,
                'auditable_id' => $leaveRequest->id,
                'description' => "Pengajuan cuti {$leaveRequest->request_number} ({$leaveRequest->employee->full_name}) di-{$newStatus} oleh {$currentUser->name} (" . str_replace('_', ' ', $currentUser->role) . ").",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return back()->with('success', "Pengajuan cuti {$leaveRequest->request_number} berhasil di-" . ($newStatus === 'approved' ? 'setujui' : 'tolak') . ".");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses persetujuan: ' . $e->getMessage());
        }
    }

    // ========================================================
    // HAK KUOTA CUTI & MASTER JENIS CUTI MANAGEMENT
    // ========================================================

    public function createType()
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->isHrAdmin()) {
            abort(403, 'Akses ditolak. Hanya HR Admin atau Super Admin yang dapat mengelola hak kuota cuti.');
        }

        return view('leaves.types.create');
    }

    public function storeType(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->isHrAdmin()) {
            abort(403, 'Akses ditolak. Hanya HR Admin atau Super Admin yang dapat mengelola hak kuota cuti.');
        }

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:leave_types,code',
            'name' => 'required|string|max:255',
            'default_quota' => 'required|integer|min:0|max:365',
            'max_consecutive_days' => 'required|integer|min:1|max:365',
            'is_paid' => 'required|boolean',
            'requires_document' => 'required|boolean',
            'gender_restriction' => 'required|in:all,male,female',
            'is_active' => 'required|boolean',
            'auto_allocate' => 'nullable|boolean',
        ]);

        $leaveType = LeaveType::create([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'default_quota' => $validated['default_quota'],
            'max_consecutive_days' => $validated['max_consecutive_days'],
            'is_paid' => (bool)$validated['is_paid'],
            'requires_document' => (bool)$validated['requires_document'],
            'gender_restriction' => $validated['gender_restriction'],
            'is_active' => (bool)$validated['is_active'],
        ]);

        $allocatedCount = 0;
        if ($request->boolean('auto_allocate') && $leaveType->is_active) {
            $year = (int)date('Y');
            $empQuery = Employee::where('status', 'active');
            if ($leaveType->gender_restriction !== 'all') {
                $empQuery->where('gender', $leaveType->gender_restriction);
            }
            $activeEmployees = $empQuery->get();

            foreach ($activeEmployees as $emp) {
                LeaveBalance::firstOrCreate(
                    [
                        'employee_id' => $emp->id,
                        'leave_type_id' => $leaveType->id,
                        'year' => $year,
                    ],
                    [
                        'initial_quota' => $leaveType->default_quota,
                        'used_quota' => 0,
                        'remaining_quota' => $leaveType->default_quota,
                    ]
                );
                $allocatedCount++;
            }
        }

        AuditLog::create([
            'user_id' => $currentUser->id,
            'event' => 'create_leave_type',
            'auditable_type' => LeaveType::class,
            'auditable_id' => $leaveType->id,
            'description' => "Menambahkan master jenis cuti baru: {$leaveType->name} ({$leaveType->code}) dengan hak kuota {$leaveType->default_quota} hari/tahun." . ($allocatedCount > 0 ? " Dialokasikan ke {$allocatedCount} karyawan." : ""),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('leaves.index', ['tab' => 'types'])->with('success', "Jenis cuti {$leaveType->name} ({$leaveType->code}) dengan hak kuota {$leaveType->default_quota} hari berhasil disimpan.");
    }

    public function editType(LeaveType $leaveType)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->isHrAdmin()) {
            abort(403, 'Akses ditolak. Hanya HR Admin atau Super Admin yang dapat mengelola hak kuota cuti.');
        }

        return view('leaves.types.edit', compact('leaveType'));
    }

    public function updateType(Request $request, LeaveType $leaveType)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->isHrAdmin()) {
            abort(403, 'Akses ditolak. Hanya HR Admin atau Super Admin yang dapat mengelola hak kuota cuti.');
        }

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:leave_types,code,' . $leaveType->id,
            'name' => 'required|string|max:255',
            'default_quota' => 'required|integer|min:0|max:365',
            'max_consecutive_days' => 'required|integer|min:1|max:365',
            'is_paid' => 'required|boolean',
            'requires_document' => 'required|boolean',
            'gender_restriction' => 'required|in:all,male,female',
            'is_active' => 'required|boolean',
            'sync_existing_balances' => 'nullable|boolean',
        ]);

        $oldQuota = $leaveType->default_quota;

        $leaveType->update([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'default_quota' => $validated['default_quota'],
            'max_consecutive_days' => $validated['max_consecutive_days'],
            'is_paid' => (bool)$validated['is_paid'],
            'requires_document' => (bool)$validated['requires_document'],
            'gender_restriction' => $validated['gender_restriction'],
            'is_active' => (bool)$validated['is_active'],
        ]);

        $syncedCount = 0;
        $year = (int)date('Y');
        if ($request->boolean('sync_existing_balances')) {
            $currentBalances = LeaveBalance::where('leave_type_id', $leaveType->id)
                ->where('year', $year)
                ->get();

            foreach ($currentBalances as $bal) {
                $newRemaining = max(0, $leaveType->default_quota - $bal->used_quota);
                $bal->update([
                    'initial_quota' => $leaveType->default_quota,
                    'remaining_quota' => $newRemaining,
                ]);
                $syncedCount++;
            }
        }

        AuditLog::create([
            'user_id' => $currentUser->id,
            'event' => 'update_leave_type',
            'auditable_type' => LeaveType::class,
            'auditable_id' => $leaveType->id,
            'description' => "Memperbarui data jenis cuti {$leaveType->name}: Hak kuota diubah dari {$oldQuota} menjadi {$leaveType->default_quota} hari/tahun." . ($syncedCount > 0 ? " Sinkronisasi ke {$syncedCount} saldo karyawan tahun {$year}." : ""),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('leaves.index', ['tab' => 'types'])->with('success', "Jenis cuti {$leaveType->name} & hak kuota berhasil diperbarui.");
    }

    public function destroyType(Request $request, LeaveType $leaveType)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->isHrAdmin()) {
            abort(403, 'Akses ditolak. Hanya HR Admin atau Super Admin yang dapat mengelola hak kuota cuti.');
        }

        if ($leaveType->requests()->count() > 0) {
            return redirect()->route('leaves.index', ['tab' => 'types'])->with('error', "Jenis cuti '{$leaveType->name}' tidak dapat dihapus karena sudah memiliki riwayat pengajuan cuti karyawan. Anda dapat mengubah statusnya menjadi 'Non-Aktif' melalui formulir Edit.");
        }

        $name = $leaveType->name;
        $leaveType->balances()->delete();
        $leaveType->delete();

        AuditLog::create([
            'user_id' => $currentUser->id,
            'event' => 'delete_leave_type',
            'auditable_type' => LeaveType::class,
            'auditable_id' => $leaveType->id,
            'description' => "Menghapus jenis cuti {$name}.",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('leaves.index', ['tab' => 'types'])->with('success', "Jenis cuti '{$name}' berhasil dihapus.");
    }

    public function createBalance()
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->isHrAdmin()) {
            abort(403, 'Akses ditolak. Hanya HR Admin atau Super Admin yang dapat mengelola saldo cuti.');
        }

        $employees = Employee::where('status', 'active')->orderBy('first_name')->orderBy('last_name')->get();
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();

        return view('leaves.balances.create', compact('employees', 'leaveTypes'));
    }

    public function storeBalance(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->isHrAdmin()) {
            abort(403, 'Akses ditolak. Hanya HR Admin atau Super Admin yang dapat mengelola saldo cuti.');
        }

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'year' => 'required|integer|min:2020|max:2050',
            'initial_quota' => 'required|integer|min:0|max:365',
        ]);

        $balance = LeaveBalance::firstOrNew([
            'employee_id' => $validated['employee_id'],
            'leave_type_id' => $validated['leave_type_id'],
            'year' => $validated['year'],
        ]);

        $balance->initial_quota = $validated['initial_quota'];
        if (!$balance->exists) {
            $balance->used_quota = 0;
            $balance->remaining_quota = $validated['initial_quota'];
        } else {
            $balance->remaining_quota = max(0, $balance->initial_quota - $balance->used_quota);
        }
        $balance->save();

        AuditLog::create([
            'user_id' => $currentUser->id,
            'event' => 'store_leave_balance',
            'auditable_type' => LeaveBalance::class,
            'auditable_id' => $balance->id,
            'description' => "Menetapkan saldo kuota {$balance->leaveType->name} untuk karyawan {$balance->employee->full_name} ({$balance->year}): Kuota {$balance->initial_quota} hari.",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('leaves.index', ['tab' => 'balances'])->with('success', "Saldo kuota cuti untuk karyawan {$balance->employee->full_name} berhasil disimpan.");
    }

    public function editBalance(LeaveBalance $leaveBalance)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->isHrAdmin()) {
            abort(403, 'Akses ditolak. Hanya HR Admin atau Super Admin yang dapat mengelola saldo cuti.');
        }

        $leaveBalance->load(['employee', 'leaveType']);

        return view('leaves.balances.edit', compact('leaveBalance'));
    }

    public function updateBalance(Request $request, LeaveBalance $leaveBalance)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->isHrAdmin()) {
            abort(403, 'Akses ditolak. Hanya HR Admin atau Super Admin yang dapat mengelola saldo cuti.');
        }

        $validated = $request->validate([
            'initial_quota' => 'required|integer|min:0|max:365',
            'used_quota' => 'required|integer|min:0|max:365',
        ]);

        $remaining = max(0, $validated['initial_quota'] - $validated['used_quota']);

        $leaveBalance->update([
            'initial_quota' => $validated['initial_quota'],
            'used_quota' => $validated['used_quota'],
            'remaining_quota' => $remaining,
        ]);

        AuditLog::create([
            'user_id' => $currentUser->id,
            'event' => 'update_leave_balance',
            'auditable_type' => LeaveBalance::class,
            'auditable_id' => $leaveBalance->id,
            'description' => "Menyesuaikan saldo cuti {$leaveBalance->employee->full_name} ({$leaveBalance->leaveType->name}): Kuota Awal {$leaveBalance->initial_quota}, Terpakai {$leaveBalance->used_quota}, Sisa {$remaining}.",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('leaves.index', ['tab' => 'balances'])->with('success', "Saldo kuota cuti {$leaveBalance->employee->full_name} ({$leaveBalance->leaveType->name}) berhasil diperbarui.");
    }

    public function destroyBalance(LeaveBalance $leaveBalance)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->isHrAdmin()) {
            abort(403, 'Akses ditolak. Hanya HR Admin atau Super Admin yang dapat menghapus saldo cuti.');
        }

        $empName = $leaveBalance->employee->full_name;
        $typeName = $leaveBalance->leaveType->name;
        $leaveBalance->delete();

        return redirect()->route('leaves.index', ['tab' => 'balances'])->with('success', "Data saldo cuti {$empName} ({$typeName}) berhasil dihapus.");
    }

    public function syncBalances(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->isHrAdmin()) {
            abort(403, 'Akses ditolak. Hanya HR Admin atau Super Admin yang dapat menjalankan sinkronisasi kuota.');
        }

        $year = (int)$request->input('year', date('Y'));
        $employees = Employee::where('status', 'active')->get();
        $leaveTypes = LeaveType::where('is_active', true)->get();

        $createdCount = 0;
        foreach ($employees as $emp) {
            foreach ($leaveTypes as $lt) {
                if ($lt->gender_restriction !== 'all' && $emp->gender !== $lt->gender_restriction) {
                    continue;
                }

                $balance = LeaveBalance::firstOrCreate(
                    [
                        'employee_id' => $emp->id,
                        'leave_type_id' => $lt->id,
                        'year' => $year,
                    ],
                    [
                        'initial_quota' => $lt->default_quota,
                        'used_quota' => 0,
                        'remaining_quota' => $lt->default_quota,
                    ]
                );

                if ($balance->wasRecentlyCreated) {
                    $createdCount++;
                }
            }
        }

        AuditLog::create([
            'user_id' => $currentUser->id,
            'event' => 'sync_leave_balances',
            'auditable_type' => LeaveBalance::class,
            'auditable_id' => 0,
            'description' => "Menjalankan sinkronisasi massal kuota cuti tahun {$year}. {$createdCount} saldo kuota baru berhasil dibuat.",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('leaves.index', ['tab' => 'balances'])->with('success', "Sinkronisasi selesai! {$createdCount} data saldo kuota baru berhasil dibuat untuk tahun {$year}.");
    }
}
