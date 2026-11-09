<?php

namespace App\Http\Controllers;

use App\Helpers\ArabicPdfHelper;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeCareerTransition;
use App\Models\PayrollTemplate;
use App\Models\Position;
use App\Models\SalaryComponent;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CareerTransitionController extends Controller
{
    /**
     * Display a listing of career transitions.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $query = EmployeeCareerTransition::with([
            'employee.department',
            'employee.position',
            'fromBranch',
            'toBranch',
            'fromDepartment',
            'toDepartment',
            'fromPosition',
            'toPosition',
            'approver',
            'applier'
        ]);

        // Role-based visibility
        if ($user->isEmployee() && !$user->canManageCareerTransitions() && !$user->isManager()) {
            $employeeId = $user->employee?->id ?? 0;
            $query->where('employee_id', $employeeId);
        } elseif ($user->isManager() && !$user->canManageCareerTransitions()) {
            $managerEmpId = $user->employee?->id;
            $deptId = $user->employee?->department_id;
            $query->where(function($q) use ($managerEmpId, $deptId) {
                $q->whereHas('employee', function($eq) use ($deptId, $managerEmpId) {
                    $eq->where('department_id', $deptId)
                       ->orWhere('manager_id', $managerEmpId);
                })->orWhere('created_by', auth()->id());
            });
        }

        // Search filter (transition number, letter number, employee name or code)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('transition_number', 'like', "%{$search}%")
                  ->orWhere('letter_number', 'like', "%{$search}%")
                  ->orWhereHas('employee', function($eq) use ($search) {
                      $eq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('employee_code', 'like', "%{$search}%");
                  });
            });
        }

        // Type filter (promotion, transfer, rotation, demotion)
        if ($request->filled('type') && in_array($request->type, ['promotion', 'transfer', 'rotation', 'demotion'])) {
            $query->where('type', $request->type);
        }

        // Status filter
        if ($request->filled('status') && in_array($request->status, ['pending', 'approved', 'applied', 'rejected', 'cancelled'])) {
            $query->where('status', $request->status);
        }

        // Branch filter
        if ($request->filled('branch_id')) {
            $branchId = $request->branch_id;
            $query->where(function($q) use ($branchId) {
                $q->where('to_branch_id', $branchId)
                  ->orWhere('from_branch_id', $branchId);
            });
        }

        // Department filter
        if ($request->filled('department_id')) {
            $deptId = $request->department_id;
            $query->where(function($q) use ($deptId) {
                $q->where('to_department_id', $deptId)
                  ->orWhere('from_department_id', $deptId);
            });
        }

        // KPI Counts
        $statsQuery = clone $query;
        // Unset status and type for global metrics of current scope
        $baseStats = (clone $query)->withoutGlobalScopes();
        $totalCount = (clone $statsQuery)->count();
        $pendingCount = (clone $statsQuery)->where('status', 'pending')->count();
        $approvedCount = (clone $statsQuery)->where('status', 'approved')->count();
        $appliedCount = (clone $statsQuery)->where('status', 'applied')->count();

        $transitions = $query->orderByDesc('id')->paginate(15)->withQueryString();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('career_transitions.index', compact(
            'transitions',
            'branches',
            'departments',
            'totalCount',
            'pendingCount',
            'approvedCount',
            'appliedCount'
        ));
    }

    /**
     * Show form for creating a new career transition.
     */
    public function create(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if (!$user->canManageCareerTransitions() && !$user->isManager()) {
            abort(403, __('Anda tidak memiliki akses untuk mengajukan mutasi atau promosi.'));
        }

        // Scope selectable employees
        $empQuery = Employee::with(['branch', 'department', 'position', 'manager', 'payrollTemplate'])
            ->where('status', 'active')
            ->orderBy('first_name');

        if ($user->isManager() && !$user->canManageCareerTransitions()) {
            $deptId = $user->employee?->department_id;
            $managerEmpId = $user->employee?->id;
            $empQuery->where(function($q) use ($deptId, $managerEmpId) {
                $q->where('department_id', $deptId)
                  ->orWhere('manager_id', $managerEmpId);
            });
        }

        $employees = $empQuery->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $positions = Position::where('is_active', true)->orderBy('title')->get();
        $managers = Employee::where('status', 'active')->orderBy('first_name')->get();
        $payrollTemplates = PayrollTemplate::where('is_active', true)->orderBy('name')->get();

        $selectedEmployeeId = $request->query('employee_id');
        $selectedEmployee = $selectedEmployeeId ? $employees->firstWhere('id', $selectedEmployeeId) : null;

        $defaultRequestDate = now()->format('Y-m-d');
        $defaultEffectiveDate = now()->addDays(7)->format('Y-m-d');

        return view('career_transitions.create', compact(
            'employees',
            'branches',
            'departments',
            'positions',
            'managers',
            'payrollTemplates',
            'selectedEmployee',
            'defaultRequestDate',
            'defaultEffectiveDate'
        ));
    }

    /**
     * Store a newly created career transition.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if (!$user->canManageCareerTransitions() && !$user->isManager()) {
            abort(403, __('Anda tidak berwenang membuat pengajuan ini.'));
        }

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|in:promotion,transfer,rotation,demotion',
            'to_branch_id' => 'nullable|exists:branches,id',
            'to_department_id' => 'nullable|exists:departments,id',
            'to_position_id' => 'nullable|exists:positions,id',
            'to_manager_id' => 'nullable|exists:employees,id',
            'to_payroll_template_id' => 'nullable|exists:payroll_templates,id',
            'to_salary' => 'nullable|numeric|min:0',
            'to_currency' => 'nullable|string|in:IDR,SAR',
            'request_date' => 'required|date',
            'effective_date' => 'required|date',
            'letter_number' => 'nullable|string|max:100',
            'reason' => 'required|string|min:5|max:2000',
            'notes' => 'nullable|string|max:2000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $employee = Employee::with(['branch', 'department', 'position', 'manager', 'payrollTemplate'])->findOrFail($validated['employee_id']);

        // Check manager authorization over this employee
        if ($user->isManager() && !$user->canManageCareerTransitions()) {
            if ($employee->department_id !== $user->employee?->department_id && $employee->manager_id !== $user->employee?->id) {
                abort(403, __('Anda hanya dapat mengajukan transisi karir untuk anggota tim di departemen Anda.'));
            }
        }

        // Determine current salary snapshot
        $currentSalary = null;
        $basicComp = $employee->compensations()->whereHas('component', function($q) {
            $q->where('type', 'earning')
              ->where(function($sq) {
                  $sq->where('code', 'like', '%BASIC%')
                     ->orWhere('name', 'like', '%Pokok%')
                     ->orWhere('name', 'like', '%أساسي%');
              });
        })->first();

        if ($basicComp) {
            $currentSalary = $basicComp->amount;
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('career_transitions', 'public');
        }

        $transition = EmployeeCareerTransition::create([
            'transition_number' => EmployeeCareerTransition::generateTransitionNumber(),
            'letter_number' => $validated['letter_number'] ?? null,
            'employee_id' => $employee->id,
            'type' => $validated['type'],
            // From snapshot
            'from_branch_id' => $employee->branch_id,
            'from_department_id' => $employee->department_id,
            'from_position_id' => $employee->position_id,
            'from_manager_id' => $employee->manager_id,
            'from_payroll_template_id' => $employee->payroll_template_id,
            'from_salary' => $currentSalary,
            'from_currency' => $employee->effective_currency,
            // To target
            'to_branch_id' => !empty($validated['to_branch_id']) ? $validated['to_branch_id'] : $employee->branch_id,
            'to_department_id' => !empty($validated['to_department_id']) ? $validated['to_department_id'] : $employee->department_id,
            'to_position_id' => !empty($validated['to_position_id']) ? $validated['to_position_id'] : $employee->position_id,
            'to_manager_id' => $validated['to_manager_id'] ?? null,
            'to_payroll_template_id' => !empty($validated['to_payroll_template_id']) ? $validated['to_payroll_template_id'] : $employee->payroll_template_id,
            'to_salary' => $validated['to_salary'] ?? null,
            'to_currency' => !empty($validated['to_currency']) ? $validated['to_currency'] : $employee->effective_currency,
            // Dates & texts
            'request_date' => $validated['request_date'],
            'effective_date' => $validated['effective_date'],
            'reason' => $validated['reason'],
            'notes' => $validated['notes'] ?? null,
            'attachment_path' => $attachmentPath,
            'status' => 'pending',
            'created_by' => $user->id,
        ]);

        return redirect()->route('career-transitions.show', $transition)
            ->with('success', __('Pengajuan :type (:num) untuk karyawan :name berhasil dibuat.', [
                'type' => $transition->type_label,
                'num' => $transition->transition_number,
                'name' => $employee->full_name
            ]));
    }

    /**
     * Display the specified career transition.
     */
    public function show(EmployeeCareerTransition $careerTransition)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Check access
        if ($user->isEmployee() && !$user->canManageCareerTransitions() && !$user->isManager()) {
            if ($careerTransition->employee_id !== $user->employee?->id) {
                abort(403, __('Anda tidak memiliki akses untuk melihat dokumen ini.'));
            }
        } elseif ($user->isManager() && !$user->canManageCareerTransitions()) {
            $deptId = $user->employee?->department_id;
            $managerEmpId = $user->employee?->id;
            if ($careerTransition->employee?->department_id !== $deptId &&
                $careerTransition->employee?->manager_id !== $managerEmpId &&
                $careerTransition->created_by !== $user->id &&
                $careerTransition->employee_id !== $user->employee?->id) {
                abort(403, __('Anda tidak memiliki akses untuk melihat dokumen ini.'));
            }
        }

        $careerTransition->load([
            'employee.branch',
            'employee.department',
            'employee.position',
            'fromBranch',
            'toBranch',
            'fromDepartment',
            'toDepartment',
            'fromPosition',
            'toPosition',
            'fromManager',
            'toManager',
            'fromPayrollTemplate',
            'toPayrollTemplate',
            'approver',
            'applier',
            'creator'
        ]);

        return view('career_transitions.show', compact('careerTransition'));
    }

    /**
     * Approve career transition proposal.
     */
    public function approve(Request $request, EmployeeCareerTransition $careerTransition)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if (!$user->canApproveCareerTransition()) {
            abort(403, __('Anda tidak berwenang menyetujui mutasi / promosi.'));
        }

        if ($careerTransition->status !== 'pending') {
            return back()->with('error', __('Hanya pengajuan dengan status Menunggu Review yang dapat disetujui.'));
        }

        $validated = $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
            'apply_immediately' => 'nullable|boolean',
        ]);

        $careerTransition->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'approval_notes' => $validated['approval_notes'] ?? null,
        ]);

        // If requested to apply immediately or user checked apply_immediately
        if ($request->boolean('apply_immediately') && $user->canManageCareerTransitions()) {
            $careerTransition->applyToEmployee($user, __('Diterapkan langsung saat persetujuan.'));
            return back()->with('success', __('Pengajuan telah disetujui dan data kepegawaian berhasil diperbarui langsung.'));
        }

        return back()->with('success', __('Pengajuan :type (:num) telah disetujui.', [
            'type' => $careerTransition->type_label,
            'num' => $careerTransition->transition_number
        ]));
    }

    /**
     * Apply the approved transition directly to Employee master data.
     */
    public function apply(Request $request, EmployeeCareerTransition $careerTransition)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if (!$user->canManageCareerTransitions()) {
            abort(403, __('Hanya HR Administrator atau Super Admin yang dapat menerapkan perubahan ke data master.'));
        }

        if (!in_array($careerTransition->status, ['pending', 'approved'])) {
            return back()->with('error', __('Pengajuan ini tidak dapat diterapkan karena statusnya :status.', [
                'status' => $careerTransition->status_label
            ]));
        }

        $validated = $request->validate([
            'execution_notes' => 'nullable|string|max:1000',
        ]);

        // If not approved yet, mark as approved first
        if ($careerTransition->status === 'pending') {
            $careerTransition->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);
        }

        $success = $careerTransition->applyToEmployee($user, $validated['execution_notes'] ?? null);

        if ($success) {
            return back()->with('success', __('Perubahan karir (:type) untuk karyawan :name telah resmi diterapkan ke data master kepegawaian.', [
                'type' => $careerTransition->type_label,
                'name' => $careerTransition->employee->full_name
            ]));
        }

        return back()->with('error', __('Gagal menerapkan perubahan ke data master pegawai.'));
    }

    /**
     * Reject career transition proposal.
     */
    public function reject(Request $request, EmployeeCareerTransition $careerTransition)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if (!$user->canApproveCareerTransition()) {
            abort(403, __('Anda tidak berwenang menolak pengajuan ini.'));
        }

        if ($careerTransition->status !== 'pending') {
            return back()->with('error', __('Hanya pengajuan dengan status Menunggu Review yang dapat ditolak.'));
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:5|max:1000',
        ]);

        $careerTransition->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return back()->with('success', __('Pengajuan :type telah ditolak.', [
            'type' => $careerTransition->type_label
        ]));
    }

    /**
     * Cancel career transition proposal.
     */
    public function cancel(EmployeeCareerTransition $careerTransition)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if ($careerTransition->created_by !== $user->id && !$user->canManageCareerTransitions()) {
            abort(403, __('Anda tidak memiliki otorisasi untuk membatalkan pengajuan ini.'));
        }

        if ($careerTransition->status !== 'pending') {
            return back()->with('error', __('Hanya pengajuan dengan status Menunggu Review yang dapat dibatalkan.'));
        }

        $careerTransition->update([
            'status' => 'cancelled'
        ]);

        return redirect()->route('career-transitions.index')
            ->with('success', __('Pengajuan :type (:num) telah dibatalkan.', [
                'type' => $careerTransition->type_label,
                'num' => $careerTransition->transition_number
            ]));
    }

    /**
     * Export official SK Mutasi / Promosi PDF.
     */
    public function exportPdf(EmployeeCareerTransition $careerTransition)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Check permission
        if (!$user->canManageCareerTransitions() &&
            $careerTransition->employee_id !== $user->employee?->id &&
            !$user->isManager()) {
            abort(403);
        }

        $careerTransition->load([
            'employee.branch',
            'employee.department',
            'employee.position',
            'fromBranch',
            'toBranch',
            'fromDepartment',
            'toDepartment',
            'fromPosition',
            'toPosition',
            'fromManager',
            'toManager',
            'approver',
            'applier'
        ]);

        $companyName = SystemSetting::get('company_name', 'PT Layang Digital Innovation');
        $companyAddress = SystemSetting::get('company_address', 'Kota Tasikmalaya, Jawa Barat, Indonesia');
        $companyPhone = SystemSetting::get('company_phone', '082116925851');
        $printedAt = now()->locale(app()->getLocale())->translatedFormat('d F Y H:i');

        $filename = 'SK_' . strtoupper($careerTransition->type) . '_' . $careerTransition->transition_number . '_' . date('Ymd');

        $pdf = ArabicPdfHelper::loadView('career_transitions.pdf.decree', compact(
            'careerTransition',
            'companyName',
            'companyAddress',
            'companyPhone',
            'printedAt'
        ), 'a4', 'portrait');

        return $pdf->download($filename . '.pdf');
    }

    /**
     * JSON API helper to return employee snapshot for dynamic form populate.
     */
    public function getEmployeeSnapshot(Employee $employee)
    {
        $employee->load(['branch', 'department', 'position', 'manager', 'payrollTemplate']);

        $basicComp = $employee->compensations()->whereHas('component', function($q) {
            $q->where('type', 'earning')
              ->where(function($sq) {
                  $sq->where('code', 'like', '%BASIC%')
                     ->orWhere('name', 'like', '%Pokok%')
                     ->orWhere('name', 'like', '%أساسي%');
              });
        })->first();

        return response()->json([
            'id' => $employee->id,
            'name' => $employee->full_name,
            'code' => $employee->employee_code,
            'branch_id' => $employee->branch_id,
            'branch_name' => $employee->branch?->name,
            'department_id' => $employee->department_id,
            'department_name' => $employee->department?->name,
            'position_id' => $employee->position_id,
            'position_title' => $employee->position?->title,
            'manager_id' => $employee->manager_id,
            'manager_name' => $employee->manager?->full_name,
            'payroll_template_id' => $employee->payroll_template_id,
            'payroll_template_name' => $employee->payrollTemplate?->name,
            'currency' => $employee->effective_currency,
            'salary' => $basicComp ? (float)$basicComp->amount : null,
            'join_date' => $employee->join_date ? $employee->join_date->format('d/m/Y') : '-',
            'tenure' => $employee->tenure_formatted,
        ]);
    }
}
