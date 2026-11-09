<?php

namespace App\Http\Controllers;

use App\Helpers\ArabicPdfHelper;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeResignation;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ResignationController extends Controller
{
    /**
     * Display a listing of employee resignations.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $query = EmployeeResignation::with([
            'employee.department',
            'employee.branch',
            'employee.position',
            'approver',
            'completer'
        ]);

        // Regular employee can only see their own
        if (!$user->canManageResignations()) {
            if (!$user->employee) {
                return redirect()->route('dashboard')->with('error', __('Profil karyawan tidak ditemukan.'));
            }
            $query->where('employee_id', $user->employee->id);
        } elseif ($user->isManager() && !$user->isHrAdmin() && !$user->isSuperAdmin()) {
            // Manager sees their department employees
            $deptId = $user->employee?->department_id;
            $query->where(function ($q) use ($deptId, $user) {
                $q->whereHas('employee', fn($eq) => $eq->where('department_id', $deptId))
                  ->orWhere('employee_id', $user->employee?->id);
            });
        }

        // Branch filter
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Department filter
        if ($request->filled('department_id')) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $request->department_id));
        }

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('resignation_number', 'like', "%{$search}%")
                  ->orWhere('reason_detail', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($eq) use ($search) {
                      $eq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('employee_code', 'like', "%{$search}%");
                  });
            });
        }

        // Counts for stats cards
        $statsQuery = EmployeeResignation::query();
        if (!$user->canManageResignations()) {
            $statsQuery->where('employee_id', $user->employee?->id ?? 0);
        } elseif ($user->isManager() && !$user->isHrAdmin() && !$user->isSuperAdmin()) {
            $deptId = $user->employee?->department_id;
            $statsQuery->where(function ($q) use ($deptId, $user) {
                $q->whereHas('employee', fn($eq) => $eq->where('department_id', $deptId))
                  ->orWhere('employee_id', $user->employee?->id);
            });
        }

        $totalCount = (clone $statsQuery)->count();
        $pendingCount = (clone $statsQuery)->where('status', 'pending')->count();
        $noticeCount = (clone $statsQuery)->where('status', 'approved')->count();
        $completedCount = (clone $statsQuery)->where('status', 'completed')->count();

        $resignations = $query->orderByDesc('id')->paginate(15)->withQueryString();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('resignations.index', compact(
            'resignations',
            'branches',
            'departments',
            'totalCount',
            'pendingCount',
            'noticeCount',
            'completedCount'
        ));
    }

    /**
     * Show the form for creating a new resignation request.
     */
    public function create()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $canSelectEmployee = $user->isHrAdmin() || $user->isSuperAdmin();
        $employees = $canSelectEmployee 
            ? Employee::where('status', 'active')->orderBy('first_name')->get() 
            : collect();

        $currentEmployee = $user->employee;
        $defaultSubmissionDate = now()->format('Y-m-d');
        $recommendedLastDay = now()->addDays(30)->format('Y-m-d');

        return view('resignations.create', compact(
            'canSelectEmployee',
            'employees',
            'currentEmployee',
            'defaultSubmissionDate',
            'recommendedLastDay'
        ));
    }

    /**
     * Store a newly created resignation request.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $rules = [
            'submission_date' => 'required|date',
            'requested_last_day' => 'required|date|after_or_equal:submission_date',
            'reason_type' => 'required|string|in:career_growth,relocation,health,personal,family,education,compensation,entrepreneurship,other',
            'reason_detail' => 'required|string|min:10',
            'handover_notes' => 'nullable|string',
            'resignation_letter' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];

        if ($user->canManageEmployees()) {
            $rules['employee_id'] = 'required|exists:employees,id';
        }

        $validated = $request->validate($rules);

        $employeeId = $user->canManageEmployees() && $request->filled('employee_id')
            ? $request->employee_id
            : $user->employee?->id;

        if (!$employeeId) {
            return back()->with('error', __('Profil karyawan tidak ditemukan.'));
        }

        $employee = Employee::findOrFail($employeeId);

        // Check if employee already has active resignation
        $existing = EmployeeResignation::where('employee_id', $employeeId)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            return back()->withInput()->with('error', __('Karyawan ini masih memiliki pengajuan pengunduran diri yang sedang aktif (:num).', ['num' => $existing->resignation_number]));
        }

        $letterPath = null;
        if ($request->hasFile('resignation_letter')) {
            $letterPath = $request->file('resignation_letter')->store('resignation_letters', 'public');
        }

        $resignation = EmployeeResignation::create([
            'resignation_number' => EmployeeResignation::generateResignationNumber(),
            'employee_id' => $employee->id,
            'branch_id' => $employee->branch_id,
            'submission_date' => $validated['submission_date'],
            'requested_last_day' => $validated['requested_last_day'],
            'reason_type' => $validated['reason_type'],
            'reason_detail' => $validated['reason_detail'],
            'handover_notes' => $validated['handover_notes'] ?? null,
            'resignation_letter_path' => $letterPath,
            'status' => 'pending',
            'created_by' => $user->id,
        ]);

        return redirect()->route('resignations.show', $resignation)
            ->with('success', __('Pengajuan pengunduran diri (:num) berhasil dikirim.', ['num' => $resignation->resignation_number]));
    }

    /**
     * Display the specified resignation request.
     */
    public function show(EmployeeResignation $resignation)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Check permission
        if (!$user->canManageResignations() && $resignation->employee_id !== $user->employee?->id) {
            abort(403, __('Anda tidak memiliki akses untuk melihat pengajuan ini.'));
        }

        if ($user->isManager() && !$user->isHrAdmin() && !$user->isSuperAdmin()) {
            if ($resignation->employee?->department_id !== $user->employee?->department_id && $resignation->employee_id !== $user->employee?->id) {
                abort(403, __('Anda tidak memiliki akses untuk melihat pengajuan ini.'));
            }
        }

        $resignation->load([
            'employee.department',
            'employee.position',
            'employee.branch',
            'employee.manager',
            'approver',
            'completer'
        ]);

        // Check if employee has active loans
        $activeLoans = $resignation->employee->loans()
            ->whereIn('status', ['active', 'disbursed'])
            ->get();

        return view('resignations.show', compact('resignation', 'activeLoans'));
    }

    /**
     * Approve the resignation request.
     */
    public function approve(Request $request, EmployeeResignation $resignation)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if (!$user->canApproveResignation()) {
            abort(403, __('Anda tidak memiliki otorisasi untuk menyetujui pengunduran diri.'));
        }

        if ($resignation->status !== 'pending') {
            return back()->with('error', __('Hanya pengajuan dengan status Menunggu Approval yang dapat disetujui.'));
        }

        $validated = $request->validate([
            'approved_last_day' => 'required|date|after_or_equal:' . $resignation->submission_date->format('Y-m-d'),
            'approval_notes' => 'nullable|string|max:1000',
            'exit_interview_notes' => 'nullable|string|max:2000',
        ]);

        $resignation->update([
            'approved_last_day' => $validated['approved_last_day'],
            'approval_notes' => $validated['approval_notes'] ?? null,
            'exit_interview_notes' => $validated['exit_interview_notes'] ?? null,
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return back()->with('success', __('Pengajuan pengunduran diri telah disetujui. Masa notice berjalan hingga :date.', [
            'date' => Carbon::parse($validated['approved_last_day'])->translatedFormat('d F Y')
        ]));
    }

    /**
     * Reject the resignation request.
     */
    public function reject(Request $request, EmployeeResignation $resignation)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if (!$user->canApproveResignation()) {
            abort(403, __('Anda tidak memiliki otorisasi untuk menolak pengunduran diri.'));
        }

        if ($resignation->status !== 'pending') {
            return back()->with('error', __('Hanya pengajuan dengan status Menunggu Approval yang dapat ditolak.'));
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:5|max:1000',
        ]);

        $resignation->update([
            'rejection_reason' => $validated['rejection_reason'],
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return back()->with('success', __('Pengajuan pengunduran diri telah ditolak.'));
    }

    /**
     * Update clearance checklist status.
     */
    public function updateClearance(Request $request, EmployeeResignation $resignation)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if (!$user->canManageEmployees() && !$user->isManager()) {
            abort(403, __('Anda tidak berwenang memperbarui checklist clearance.'));
        }

        $validated = $request->validate([
            'clearance_assets_returned' => 'nullable',
            'clearance_assets_notes' => 'nullable|string|max:1000',
            'clearance_finance_settled' => 'nullable',
            'clearance_finance_notes' => 'nullable|string|max:1000',
            'clearance_handover_completed' => 'nullable',
            'clearance_handover_notes' => 'nullable|string|max:1000',
        ]);

        $resignation->update([
            'clearance_assets_returned' => (bool)$request->input('clearance_assets_returned', 0),
            'clearance_assets_notes' => $validated['clearance_assets_notes'] ?? null,
            'clearance_finance_settled' => (bool)$request->input('clearance_finance_settled', 0),
            'clearance_finance_notes' => $validated['clearance_finance_notes'] ?? null,
            'clearance_handover_completed' => (bool)$request->input('clearance_handover_completed', 0),
            'clearance_handover_notes' => $validated['clearance_handover_notes'] ?? null,
        ]);

        return back()->with('success', __('Checklist clearance offboarding berhasil diperbarui (:pct%).', [
            'pct' => $resignation->fresh()->clearance_percentage
        ]));
    }

    /**
     * Complete the offboarding process and deactivate employee.
     */
    public function complete(Request $request, EmployeeResignation $resignation)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if (!$user->canManageEmployees()) {
            abort(403, __('Hanya HR Admin atau Super Admin yang dapat menyelesaikan offboarding.'));
        }

        if ($resignation->status !== 'approved') {
            return back()->with('error', __('Pengunduran diri harus disetujui terlebih dahulu sebelum offboarding diselesaikan.'));
        }

        $validated = $request->validate([
            'completion_notes' => 'nullable|string|max:1000',
        ]);

        $lastDay = $resignation->effective_last_day;

        $resignation->update([
            'status' => 'completed',
            'completed_by' => $user->id,
            'completed_at' => now(),
            'completion_notes' => $validated['completion_notes'] ?? null,
        ]);

        // Update Employee record status & end_date
        $resignation->employee->update([
            'status' => 'inactive',
            'end_date' => $lastDay,
        ]);

        return back()->with('success', __('Proses offboarding karyawan :name telah selesai dan status kepegawaian dinonaktifkan.', [
            'name' => $resignation->employee->full_name
        ]));
    }

    /**
     * Cancel resignation request.
     */
    public function cancel(Request $request, EmployeeResignation $resignation)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if ($resignation->employee_id !== $user->employee?->id && !$user->canManageEmployees()) {
            abort(403);
        }

        if ($resignation->status !== 'pending') {
            return back()->with('error', __('Hanya pengajuan yang masih berstatus pending yang dapat dibatalkan.'));
        }

        $resignation->update([
            'status' => 'cancelled',
        ]);

        return redirect()->route('resignations.index')
            ->with('success', __('Pengajuan pengunduran diri telah dibatalkan.'));
    }

    /**
     * Export official Resignation & Clearance Certificate PDF.
     */
    public function exportPdf(EmployeeResignation $resignation)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if (!$user->canManageResignations() && $resignation->employee_id !== $user->employee?->id) {
            abort(403);
        }

        $resignation->load([
            'employee.department',
            'employee.position',
            'employee.branch',
            'approver',
            'completer'
        ]);

        $companyName = SystemSetting::get('company_name', 'PT Layang Digital Innovation');
        $companyAddress = SystemSetting::get('company_address', 'Kota Tasikmalaya, Jawa Barat, Indonesia');
        $companyPhone = SystemSetting::get('company_phone', '082116925851');
        $printedAt = now()->locale(app()->getLocale())->translatedFormat('d F Y H:i');

        $filename = 'Clearance_Resign_' . $resignation->resignation_number . '_' . date('Ymd');

        $pdf = ArabicPdfHelper::loadView('resignations.pdf.clearance_certificate', compact(
            'resignation',
            'companyName',
            'companyAddress',
            'companyPhone',
            'printedAt'
        ), 'a4', 'portrait');

        return $pdf->download($filename . '.pdf');
    }
}

