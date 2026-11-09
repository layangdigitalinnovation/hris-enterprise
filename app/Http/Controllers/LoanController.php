<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeLoan;
use App\Models\EmployeeLoanInstallment;
use App\Models\SystemSetting;
use App\Helpers\ArabicPdfHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LoanController extends Controller
{
    /**
     * Display a listing of employee loans.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $query = EmployeeLoan::with(['employee.department', 'employee.branch', 'approver', 'disburser']);

        // Non-management employees can only view their own loans
        if (!$user->canManageLoans()) {
            if (!$user->employee) {
                return redirect()->route('dashboard')->with('error', 'Profil karyawan tidak ditemukan.');
            }
            $query->where('employee_id', $user->employee->id);
        } else {
            // Filter by branch
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
        }

        // Filter by Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by Currency
        if ($request->filled('currency') && $request->currency !== 'all') {
            $query->where('currency', $request->currency);
        }

        // Search Keyword
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('loan_number', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($eq) use ($search) {
                      $eq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('employee_code', 'like', "%{$search}%");
                  });
            });
        }

        // Dual-Currency Metrics calculations
        $metricsBase = EmployeeLoan::query();
        if (!$user->canManageLoans() && $user->employee) {
            $metricsBase->where('employee_id', $user->employee->id);
        }

        $metrics = [
            'pending_count' => (clone $metricsBase)->where('status', 'pending')->count(),
            'pending_idr' => (float) (clone $metricsBase)->where('status', 'pending')->where('currency', 'IDR')->sum('amount'),
            'pending_sar' => (float) (clone $metricsBase)->where('status', 'pending')->where('currency', 'SAR')->sum('amount'),

            'approved_count' => (clone $metricsBase)->where('status', 'approved')->count(),
            'approved_idr' => (float) (clone $metricsBase)->where('status', 'approved')->where('currency', 'IDR')->sum('approved_amount'),
            'approved_sar' => (float) (clone $metricsBase)->where('status', 'approved')->where('currency', 'SAR')->sum('approved_amount'),

            'active_count' => (clone $metricsBase)->whereIn('status', ['active', 'disbursed'])->count(),
            'active_idr' => (float) (clone $metricsBase)->whereIn('status', ['active', 'disbursed'])->where('currency', 'IDR')->sum('approved_amount'),
            'active_sar' => (float) (clone $metricsBase)->whereIn('status', ['active', 'disbursed'])->where('currency', 'SAR')->sum('approved_amount'),

            'completed_count' => (clone $metricsBase)->where('status', 'completed')->count(),
            'completed_idr' => (float) (clone $metricsBase)->where('status', 'completed')->where('currency', 'IDR')->sum('approved_amount'),
            'completed_sar' => (float) (clone $metricsBase)->where('status', 'completed')->where('currency', 'SAR')->sum('approved_amount'),

            'total_count' => (clone $metricsBase)->count(),
            'total_idr' => (float) (clone $metricsBase)->where('currency', 'IDR')->sum('amount'),
            'total_sar' => (float) (clone $metricsBase)->where('currency', 'SAR')->sum('amount'),
        ];

        $loans = $query->latest()->paginate(10)->withQueryString();
        $branches = Branch::all();

        return view('loans.index', compact('loans', 'metrics', 'branches'));
    }

    /**
     * Show the form for creating a new loan request.
     */
    public function create()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $employees = [];
        $selectedEmployee = null;

        if ($user->canManageLoans()) {
            $employees = Employee::where('status', 'active')->with(['branch', 'department'])->get();
        } else {
            $selectedEmployee = $user->employee;
            if (!$selectedEmployee) {
                return redirect()->route('loans.index')->with('error', 'Akun Anda belum terhubung dengan data profil karyawan.');
            }
        }

        return view('loans.create', compact('employees', 'selectedEmployee'));
    }

    /**
     * Store a newly created loan request in storage.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $rules = [
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|in:IDR,SAR',
            'tenor_months' => 'required|integer|min:1|max:60',
            'interest_rate_percent' => 'nullable|numeric|min:0|max:100',
            'repayment_start_date' => 'required|date',
            'reason' => 'required|string|max:1000',
            'payroll_deduction' => 'nullable|boolean',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];

        if ($user->canManageLoans()) {
            $rules['employee_id'] = 'required|exists:employees,id';
        }

        $validated = $request->validate($rules);

        $employeeId = $user->canManageLoans() ? $validated['employee_id'] : $user->employee?->id;
        if (!$employeeId) {
            return back()->with('error', 'Karyawan pemohon tidak valid.');
        }

        $employee = Employee::findOrFail($employeeId);
        $amount = (float) $validated['amount'];
        $tenor = (int) $validated['tenor_months'];
        $interest = isset($validated['interest_rate_percent']) ? (float) $validated['interest_rate_percent'] : 0.00;
        $monthly = round($amount / $tenor, 2);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('loans/attachments', 'public');
        }

        DB::beginTransaction();
        try {
            $loan = EmployeeLoan::create([
                'loan_number' => EmployeeLoan::generateLoanNumber(),
                'employee_id' => $employee->id,
                'branch_id' => $employee->branch_id,
                'currency' => $validated['currency'],
                'amount' => $amount,
                'approved_amount' => $amount,
                'tenor_months' => $tenor,
                'interest_rate_percent' => $interest,
                'monthly_installment' => $monthly,
                'repayment_start_date' => $validated['repayment_start_date'],
                'reason' => $validated['reason'],
                'payroll_deduction' => $request->has('payroll_deduction') ? (bool) $request->payroll_deduction : true,
                'status' => 'pending',
                'attachment_file' => $attachmentPath,
                'created_by' => $user->id,
            ]);

            // Auto generate initial installment schedule
            $loan->generateInstallments();

            AuditLog::create([
                'user_id' => $user->id,
                'event' => 'create_employee_loan',
                'auditable_type' => EmployeeLoan::class,
                'auditable_id' => $loan->id,
                'description' => "Membuat pengajuan pinjaman karyawan {$loan->loan_number} sebesar " . format_currency($amount, $loan->currency) . " untuk {$employee->full_name}.",
            ]);

            DB::commit();

            return redirect()->route('loans.show', $loan)->with('success', "Pengajuan pinjaman {$loan->loan_number} berhasil dibuat!");
        } catch (\Exception $e) {
            DB::rollBack();
            if ($attachmentPath) {
                Storage::disk('public')->delete($attachmentPath);
            }
            return back()->withInput()->with('error', 'Gagal membuat pengajuan pinjaman: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified loan details and installment schedule.
     */
    public function show(EmployeeLoan $loan)
    {
        $this->authorizeView($loan);

        $loan->load(['employee.department', 'employee.branch', 'approver', 'disburser', 'installments.payrollPeriod', 'installments.recorder']);

        return view('loans.show', compact('loan'));
    }

    /**
     * Show the form for editing the loan request.
     */
    public function edit(EmployeeLoan $loan)
    {
        $this->authorizeView($loan);

        if ($loan->status !== 'pending') {
            return redirect()->route('loans.show', $loan)->with('error', 'Pinjaman yang sudah diproses atau disetujui tidak dapat diubah lagi.');
        }

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $employees = $user->canManageLoans() ? Employee::where('status', 'active')->with(['branch', 'department'])->get() : [];

        return view('loans.edit', compact('loan', 'employees'));
    }

    /**
     * Update the specified loan in storage.
     */
    public function update(Request $request, EmployeeLoan $loan)
    {
        $this->authorizeView($loan);

        if ($loan->status !== 'pending') {
            return redirect()->route('loans.show', $loan)->with('error', 'Pinjaman yang sudah diproses atau disetujui tidak dapat diubah lagi.');
        }

        /** @var \App\Models\User $user */
        $user = auth()->user();

        $rules = [
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|in:IDR,SAR',
            'tenor_months' => 'required|integer|min:1|max:60',
            'interest_rate_percent' => 'nullable|numeric|min:0|max:100',
            'repayment_start_date' => 'required|date',
            'reason' => 'required|string|max:1000',
            'payroll_deduction' => 'nullable|boolean',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];

        if ($user->canManageLoans()) {
            $rules['employee_id'] = 'required|exists:employees,id';
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            if ($user->canManageLoans() && isset($validated['employee_id'])) {
                $employee = Employee::findOrFail($validated['employee_id']);
                $loan->employee_id = $employee->id;
                $loan->branch_id = $employee->branch_id;
            }

            $amount = (float) $validated['amount'];
            $tenor = (int) $validated['tenor_months'];
            $monthly = round($amount / $tenor, 2);

            $loan->currency = $validated['currency'];
            $loan->amount = $amount;
            $loan->approved_amount = $amount;
            $loan->tenor_months = $tenor;
            $loan->interest_rate_percent = isset($validated['interest_rate_percent']) ? (float) $validated['interest_rate_percent'] : 0.00;
            $loan->monthly_installment = $monthly;
            $loan->repayment_start_date = $validated['repayment_start_date'];
            $loan->reason = $validated['reason'];
            $loan->payroll_deduction = $request->has('payroll_deduction') ? (bool) $request->payroll_deduction : true;

            if ($request->hasFile('attachment')) {
                if ($loan->attachment_file) {
                    Storage::disk('public')->delete($loan->attachment_file);
                }
                $loan->attachment_file = $request->file('attachment')->store('loans/attachments', 'public');
            }

            $loan->save();

            // Regenerate installment schedule
            $loan->generateInstallments();

            AuditLog::create([
                'user_id' => $user->id,
                'event' => 'update_employee_loan',
                'auditable_type' => EmployeeLoan::class,
                'auditable_id' => $loan->id,
                'description' => "Memperbarui pengajuan pinjaman {$loan->loan_number}.",
            ]);

            DB::commit();

            return redirect()->route('loans.show', $loan)->with('success', 'Data pengajuan pinjaman berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui pinjaman: ' . $e->getMessage());
        }
    }

    /**
     * Remove the loan from storage (only pending or rejected).
     */
    public function destroy(EmployeeLoan $loan)
    {
        $this->authorizeView($loan);

        if (!in_array($loan->status, ['pending', 'rejected'])) {
            return back()->with('error', 'Pinjaman yang sudah disetujui atau dicairkan tidak dapat dihapus.');
        }

        DB::beginTransaction();
        try {
            if ($loan->attachment_file) {
                Storage::disk('public')->delete($loan->attachment_file);
            }
            if ($loan->disbursement_proof) {
                Storage::disk('public')->delete($loan->disbursement_proof);
            }

            $loan->installments()->delete();
            $num = $loan->loan_number;
            $loan->delete();

            AuditLog::create([
                'user_id' => auth()->id(),
                'event' => 'delete_employee_loan',
                'description' => "Menghapus pengajuan pinjaman {$num}.",
            ]);

            DB::commit();

            return redirect()->route('loans.index')->with('success', "Pengajuan pinjaman {$num} berhasil dihapus.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus pinjaman: ' . $e->getMessage());
        }
    }

    /**
     * Dedicated approval page (Non-modal).
     */
    public function approveForm(EmployeeLoan $loan)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if (!$user->canApproveLoan()) {
            abort(403, 'Akses ditolak. Anda tidak berwenang menyetujui pinjaman karyawan.');
        }

        if ($loan->status !== 'pending') {
            return redirect()->route('loans.show', $loan)->with('error', 'Pinjaman ini tidak dalam status menunggu persetujuan.');
        }

        $loan->load(['employee.department', 'employee.branch']);

        return view('loans.approve', compact('loan'));
    }

    /**
     * Process loan approval.
     */
    public function approve(Request $request, EmployeeLoan $loan)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if (!$user->canApproveLoan()) {
            abort(403, 'Akses ditolak. Anda tidak berwenang menyetujui pinjaman karyawan.');
        }

        if ($loan->status !== 'pending') {
            return redirect()->route('loans.show', $loan)->with('error', 'Pinjaman ini tidak dalam status menunggu persetujuan.');
        }

        $validated = $request->validate([
            'approval_action' => 'required|in:full,partial',
            'approved_amount' => 'required|numeric|min:1|max:' . $loan->amount,
            'tenor_months' => 'required|integer|min:1|max:60',
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        $approvedAmount = ($validated['approval_action'] === 'full') ? (float) $loan->amount : (float) $validated['approved_amount'];
        $tenor = (int) $validated['tenor_months'];
        $monthly = round($approvedAmount / $tenor, 2);

        DB::beginTransaction();
        try {
            $loan->status = 'approved';
            $loan->approved_amount = $approvedAmount;
            $loan->tenor_months = $tenor;
            $loan->monthly_installment = $monthly;
            $loan->approval_notes = $validated['approval_notes'];
            $loan->approved_by = $user->id;
            $loan->approved_at = now();
            $loan->save();

            // Regenerate installment schedule based on final approved amount and tenor
            $loan->generateInstallments();

            AuditLog::create([
                'user_id' => $user->id,
                'event' => 'approve_employee_loan',
                'auditable_type' => EmployeeLoan::class,
                'auditable_id' => $loan->id,
                'description' => "Menyetujui pengajuan pinjaman {$loan->loan_number} sebesar " . format_currency($approvedAmount, $loan->currency) . " ({$tenor} bulan).",
            ]);

            DB::commit();

            return redirect()->route('loans.show', $loan)->with('success', "Pinjaman {$loan->loan_number} berhasil disetujui! Siap untuk diproses pencairan oleh tim Keuangan.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses persetujuan pinjaman: ' . $e->getMessage());
        }
    }

    /**
     * Dedicated rejection page (Non-modal).
     */
    public function rejectForm(EmployeeLoan $loan)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if (!$user->canApproveLoan()) {
            abort(403, 'Akses ditolak. Anda tidak berwenang menolak pinjaman karyawan.');
        }

        if ($loan->status !== 'pending') {
            return redirect()->route('loans.show', $loan)->with('error', 'Pinjaman ini tidak dalam status menunggu persetujuan.');
        }

        $loan->load(['employee.department', 'employee.branch']);

        return view('loans.reject', compact('loan'));
    }

    /**
     * Process loan rejection.
     */
    public function reject(Request $request, EmployeeLoan $loan)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if (!$user->canApproveLoan()) {
            abort(403, 'Akses ditolak. Anda tidak berwenang menolak pinjaman karyawan.');
        }

        if ($loan->status !== 'pending') {
            return redirect()->route('loans.show', $loan)->with('error', 'Pinjaman ini tidak dalam status menunggu persetujuan.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $loan->status = 'rejected';
            $loan->rejection_reason = $validated['rejection_reason'];
            $loan->approved_by = $user->id;
            $loan->approved_at = now();
            $loan->save();

            AuditLog::create([
                'user_id' => $user->id,
                'event' => 'reject_employee_loan',
                'auditable_type' => EmployeeLoan::class,
                'auditable_id' => $loan->id,
                'description' => "Menolak pengajuan pinjaman {$loan->loan_number}. Alasan: {$validated['rejection_reason']}",
            ]);

            DB::commit();

            return redirect()->route('loans.show', $loan)->with('success', "Pengajuan pinjaman {$loan->loan_number} telah ditolak.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menolak pinjaman: ' . $e->getMessage());
        }
    }

    /**
     * Dedicated disbursement page (Non-modal).
     */
    public function disburseForm(EmployeeLoan $loan)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if (!$user->canDisburseLoan()) {
            abort(403, 'Akses ditolak. Hanya Tim Keuangan (Finance) atau Super Admin yang berwenang mencairkan dana pinjaman.');
        }

        if ($loan->status !== 'approved') {
            return redirect()->route('loans.show', $loan)->with('error', 'Hanya pinjaman berstatus Disetujui (Approved) yang dapat dicairkan.');
        }

        $loan->load(['employee.department', 'employee.branch']);

        return view('loans.disburse', compact('loan'));
    }

    /**
     * Process loan disbursement.
     */
    public function disburse(Request $request, EmployeeLoan $loan)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if (!$user->canDisburseLoan()) {
            abort(403, 'Akses ditolak. Hanya Tim Keuangan (Finance) atau Super Admin yang berwenang mencairkan dana pinjaman.');
        }

        if ($loan->status !== 'approved') {
            return redirect()->route('loans.show', $loan)->with('error', 'Hanya pinjaman berstatus Disetujui (Approved) yang dapat dicairkan.');
        }

        $validated = $request->validate([
            'disbursed_at' => 'required|date',
            'payment_method' => 'required|in:bank_transfer,cash,payroll',
            'disbursement_reference' => 'nullable|string|max:100',
            'disbursement_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'disbursement_notes' => 'nullable|string|max:1000',
        ]);

        $proofPath = null;
        if ($request->hasFile('disbursement_proof')) {
            $proofPath = $request->file('disbursement_proof')->store('loans/disbursements', 'public');
        }

        DB::beginTransaction();
        try {
            $loan->status = 'active'; // Mark as active (funds disbursed, installment begins)
            $loan->disbursed_at = $validated['disbursed_at'];
            $loan->disbursed_by = $user->id;
            $loan->payment_method = $validated['payment_method'];
            $loan->disbursement_reference = $validated['disbursement_reference'] ?? null;
            $loan->disbursement_notes = $validated['disbursement_notes'] ?? null;
            if ($proofPath) {
                $loan->disbursement_proof = $proofPath;
            }
            $loan->save();

            AuditLog::create([
                'user_id' => $user->id,
                'event' => 'disburse_employee_loan',
                'auditable_type' => EmployeeLoan::class,
                'auditable_id' => $loan->id,
                'description' => "Mencairkan dana pinjaman {$loan->loan_number} sebesar " . format_currency($loan->effective_principal, $loan->currency) . " melalui metode {$loan->payment_method}.",
            ]);

            DB::commit();

            return redirect()->route('loans.show', $loan)->with('success', "Dana pinjaman {$loan->loan_number} berhasil dicairkan! Status pinjaman kini Aktif berjalan.");
        } catch (\Exception $e) {
            DB::rollBack();
            if ($proofPath) {
                Storage::disk('public')->delete($proofPath);
            }
            return back()->with('error', 'Gagal memproses pencairan dana: ' . $e->getMessage());
        }
    }

    /**
     * Dedicated repayment page (Non-modal).
     */
    public function repayForm(EmployeeLoan $loan)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if (!$user->canManageLoans()) {
            abort(403, 'Akses ditolak. Anda tidak berwenang mencatat pembayaran pinjaman.');
        }

        if (!in_array($loan->status, ['active', 'disbursed'])) {
            return redirect()->route('loans.show', $loan)->with('error', 'Hanya pinjaman yang sedang aktif yang dapat menerima pencatatan pembayaran.');
        }

        $loan->load(['employee', 'installments']);
        $nextInstallment = $loan->installments->where('status', 'unpaid')->first();
        $remainingPrincipal = $loan->remaining_amount;

        return view('loans.repay', compact('loan', 'nextInstallment', 'remainingPrincipal'));
    }

    /**
     * Process manual repayment / early settlement.
     */
    public function repay(Request $request, EmployeeLoan $loan)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if (!$user->canManageLoans()) {
            abort(403, 'Akses ditolak. Anda tidak berwenang mencatat pembayaran pinjaman.');
        }

        if (!in_array($loan->status, ['active', 'disbursed'])) {
            return redirect()->route('loans.show', $loan)->with('error', 'Hanya pinjaman yang sedang aktif yang dapat menerima pembayaran.');
        }

        $validated = $request->validate([
            'repay_type' => 'required|in:single_installment,full_settlement,custom_amount',
            'amount' => 'required|numeric|min:1',
            'paid_date' => 'required|date',
            'payment_method' => 'required|in:bank_transfer,cash,payroll',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
            'proof_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $proofPath = null;
        if ($request->hasFile('proof_file')) {
            $proofPath = $request->file('proof_file')->store('loans/repayments', 'public');
        }

        DB::beginTransaction();
        try {
            $amount = (float) $validated['amount'];
            $loan->recordRepayment(
                $amount,
                $validated['payment_method'],
                $validated['paid_date'],
                $user->id,
                $validated['reference_number'] ?? null,
                $proofPath,
                $validated['notes'] ?? null
            );

            AuditLog::create([
                'user_id' => $user->id,
                'event' => 'repay_employee_loan',
                'auditable_type' => EmployeeLoan::class,
                'auditable_id' => $loan->id,
                'description' => "Mencatat pembayaran pinjaman {$loan->loan_number} sebesar " . format_currency($amount, $loan->currency) . " ({$validated['payment_method']}).",
            ]);

            DB::commit();

            return redirect()->route('loans.show', $loan)->with('success', "Pembayaran pinjaman {$loan->loan_number} sebesar " . format_currency($amount, $loan->currency) . " berhasil dicatat!");
        } catch (\Exception $e) {
            DB::rollBack();
            if ($proofPath) {
                Storage::disk('public')->delete($proofPath);
            }
            return back()->with('error', 'Gagal mencatat pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Export official Loan Agreement PDF.
     */
    public function exportPdf(EmployeeLoan $loan)
    {
        $this->authorizeView($loan);

        $loan->load(['employee.department', 'employee.position', 'employee.branch', 'approver.employee.position', 'disburser.employee.position', 'installments']);

        // 1. Resolve HR Manager (Menyetujui - Manajemen)
        if ($loan->approved_by && $loan->approver) {
            $hrManagerName = $loan->approver->employee?->full_name ?? preg_replace('/\s*\(.*?\)/', '', $loan->approver->name);
            $hrManagerTitle = $loan->approver->employee?->position?->title ?? 'HR Manager';
        } else {
            $hrEmp = Employee::whereHas('position', function ($q) {
                $q->where('code', 'MGR-HR')
                  ->orWhere('title', 'like', '%HR Manager%')
                  ->orWhere('title', 'like', '%Manager HR%');
            })->where('status', 'active')->first();

            if (!$hrEmp) {
                $hrUser = \App\Models\User::where('role', 'hr_admin')->where('is_active', true)->with('employee.position')->first();
                $hrEmp = $hrUser?->employee;
            }

            $hrManagerName = $hrEmp ? $hrEmp->full_name : 'Siti Rahmawati';
            $hrManagerTitle = $hrEmp?->position?->title ?? 'HR Manager';
        }

        // 2. Resolve Finance Manager / Finance Officer (Mengetahui - Pencairan)
        if ($loan->disbursed_by && $loan->disburser) {
            $financeManagerName = $loan->disburser->employee?->full_name ?? preg_replace('/\s*\(.*?\)/', '', $loan->disburser->name);
            $financeManagerTitle = $loan->disburser->employee?->position?->title ?? 'Finance Manager';
        } else {
            $finEmp = Employee::whereHas('position', function ($q) {
                $q->where('code', 'MGR-FIN')
                  ->orWhere('title', 'like', '%Finance Manager%')
                  ->orWhere('title', 'like', '%Manager Finance%');
            })->where('status', 'active')->first();

            if (!$finEmp) {
                $finUser = \App\Models\User::where('role', 'finance')->where('is_active', true)->with('employee.position')->first();
                $finEmp = $finUser?->employee;
            }

            $financeManagerName = $finEmp ? $finEmp->full_name : 'Bambang Sudirman';
            $financeManagerTitle = $finEmp?->position?->title ?? 'Finance Manager';
        }

        $companyName = SystemSetting::get('company_name', 'PT Layang Digital Innovation');
        $companyAddress = SystemSetting::get('company_address', 'Kota Tasikmalaya, Jawa Barat, Indonesia');
        $companyPhone = SystemSetting::get('company_phone', '082116925851');

        $pdf = ArabicPdfHelper::loadView('reports.pdf.loan_agreement', compact(
            'loan',
            'companyName',
            'companyAddress',
            'companyPhone',
            'hrManagerName',
            'hrManagerTitle',
            'financeManagerName',
            'financeManagerTitle'
        ), 'a4', 'portrait');

        $filename = 'Perjanjian_Pinjaman_' . str_replace(['/', '\\', ' '], '_', $loan->loan_number) . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Authorize user to view the given loan.
     */
    private function authorizeView(EmployeeLoan $loan): void
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if ($user->canManageLoans()) {
            return;
        }

        if ($user->employee && $loan->employee_id === $user->employee->id) {
            return;
        }

        abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk melihat data pinjaman ini.');
    }
}

