<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Position;
use App\Models\ReimbursementCategory;
use App\Models\ReimbursementRequest;
use App\Models\SystemSetting;
use App\Models\User;
use App\Helpers\ArabicPdfHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReimbursementController extends Controller
{
    /**
     * Dashboard & Daftar Reimbursement
     */
    public function index(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        $query = ReimbursementRequest::with(['employee.department', 'employee.branch', 'category', 'approver', 'payer']);

        // Scope by Role
        $isEmployeeOnly = $currentUser->isEmployee();
        $isManager = ($currentUser->role === 'manager' && $currentUser->employee?->department_id);

        if ($isEmployeeOnly) {
            $employeeId = $currentUser->employee?->id ?? 0;
            $query->where('employee_id', $employeeId);
        } elseif ($isManager) {
            $deptId = $currentUser->employee->department_id;
            $query->where(function ($q) use ($deptId, $currentUser) {
                $q->whereHas('employee', fn($eq) => $eq->where('department_id', $deptId))
                  ->orWhere('employee_id', $currentUser->employee?->id ?? 0);
            });
        }

        // Pencarian (Search)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('claim_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($eq) use ($search) {
                      $eq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('employee_code', 'like', "%{$search}%");
                  });
            });
        }

        // Filter Status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter Kategori
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Filter Cabang / Proyek
        if ($branchId = $request->input('branch_id')) {
            $query->where('branch_id', $branchId);
        }

        // Filter Mata Uang (IDR / SAR)
        if ($currency = $request->input('currency')) {
            if (in_array(strtoupper($currency), ['IDR', 'SAR'])) {
                $query->where('currency', strtoupper($currency));
            }
        }

        // Filter Rentang Tanggal
        if ($startDate = $request->input('start_date')) {
            $query->whereDate('claim_date', '>=', $startDate);
        }
        if ($endDate = $request->input('end_date')) {
            $query->whereDate('claim_date', '<=', $endDate);
        }

        // Pagination Per Halaman
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [5, 10, 15, 25, 50])) {
            $perPage = 10;
        }

        $reimbursements = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        // Hitung Ringkasan Finansial untuk Kartu Statistik (Diselaraskan dengan cakupan role)
        $statsBase = ReimbursementRequest::query();
        if ($isEmployeeOnly) {
            $statsBase->where('employee_id', $currentUser->employee?->id ?? 0);
        } elseif ($isManager) {
            $deptId = $currentUser->employee->department_id;
            $statsBase->where(function ($q) use ($deptId, $currentUser) {
                $q->whereHas('employee', fn($eq) => $eq->where('department_id', $deptId))
                  ->orWhere('employee_id', $currentUser->employee?->id ?? 0);
            });
        }

        $statsPending = (clone $statsBase)->where('status', 'pending');
        $statsApproved = (clone $statsBase)->where('status', 'approved');
        $statsPaid = (clone $statsBase)->where('status', 'paid');
        $statsRejected = (clone $statsBase)->where('status', 'rejected');

        $metrics = [
            'pending_count' => (clone $statsPending)->count(),
            'pending_idr' => (clone $statsPending)->where('currency', 'IDR')->sum('amount'),
            'pending_sar' => (clone $statsPending)->where('currency', 'SAR')->sum('amount'),

            'approved_count' => (clone $statsApproved)->count(),
            'approved_idr' => (clone $statsApproved)->where('currency', 'IDR')->sum(DB::raw('COALESCE(approved_amount, amount)')),
            'approved_sar' => (clone $statsApproved)->where('currency', 'SAR')->sum(DB::raw('COALESCE(approved_amount, amount)')),

            'paid_count' => (clone $statsPaid)->count(),
            'paid_idr' => (clone $statsPaid)->where('currency', 'IDR')->sum(DB::raw('COALESCE(approved_amount, amount)')),
            'paid_sar' => (clone $statsPaid)->where('currency', 'SAR')->sum(DB::raw('COALESCE(approved_amount, amount)')),

            'total_count' => (clone $statsBase)->count(),
            'total_idr' => (clone $statsBase)->where('currency', 'IDR')->sum(DB::raw('COALESCE(approved_amount, amount)')),
            'total_sar' => (clone $statsBase)->where('currency', 'SAR')->sum(DB::raw('COALESCE(approved_amount, amount)')),
        ];

        $categories = ReimbursementCategory::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('reimbursements.index', compact(
            'reimbursements',
            'metrics',
            'categories',
            'branches',
            'isEmployeeOnly',
            'isManager'
        ));
    }

    /**
     * Form Pengajuan Reimbursement Baru
     */
    public function create()
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        $categories = ReimbursementCategory::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        // Karyawan: jika HR/Admin bisa memilih karyawan, jika Employee terkunci ke profil sendiri
        $isHrAdmin = $currentUser->canManageEmployees() || $currentUser->isFinance();
        $currentEmployee = $currentUser->employee;

        $employees = $isHrAdmin
            ? Employee::with(['branch', 'department'])->where('status', 'active')->orderBy('first_name')->get()
            : collect([$currentEmployee]);

        return view('reimbursements.create', compact(
            'categories',
            'branches',
            'employees',
            'currentEmployee',
            'isHrAdmin'
        ));
    }

    /**
     * Simpan Pengajuan Reimbursement Baru
     */
    public function store(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        $isHrAdmin = $currentUser->canManageEmployees() || $currentUser->isFinance();

        $rules = [
            'category_id' => 'required|exists:reimbursement_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'claim_date' => 'required|date|before_or_equal:today',
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|in:IDR,SAR',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ];

        if ($isHrAdmin) {
            $rules['employee_id'] = 'required|exists:employees,id';
        }

        $validated = $request->validate($rules);

        // Tentukan Pegawai & Cabang
        if ($isHrAdmin) {
            $employee = Employee::findOrFail($validated['employee_id']);
        } else {
            $employee = $currentUser->employee;
            if (!$employee) {
                return back()->with('error', 'Akun Anda tidak terhubung dengan profil pegawai aktif.');
            }
        }

        $category = ReimbursementCategory::findOrFail($validated['category_id']);

        // Validasi kesesuaian mata uang kategori
        if (!$category->supportsCurrency($validated['currency'])) {
            return back()->withErrors(['category_id' => "Kategori '{$category->name}' hanya berlaku untuk klaim mata uang {$category->currency}."])->withInput();
        }

        // Validasi batas maksimal nominal (plafon) jika ada
        $limit = $category->getMaxAmountForCurrency($validated['currency']);
        if ($limit !== null && $validated['amount'] > $limit) {
            $formattedLimit = format_currency($limit, $validated['currency']);
            return back()->withErrors(['amount' => "Nominal pengajuan melebihi batas maksimal kategori '{$category->name}' ({$formattedLimit})."])->withInput();
        }

        // Validasi kuitansi jika kategori mewajibkannya
        if ($category->requires_receipt && !$request->hasFile('receipt')) {
            return back()->withErrors(['receipt' => "Kategori '{$category->name}' mewajibkan unggah bukti struk/kuitansi."])->withInput();
        }

        // Upload Receipt File jika ada
        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('reimbursements/receipts', 'public');
        }

        // Generate Claim Number unik
        $claimNumber = ReimbursementRequest::generateClaimNumber();

        $reimbursement = ReimbursementRequest::create([
            'claim_number' => $claimNumber,
            'employee_id' => $employee->id,
            'branch_id' => $employee->branch_id,
            'category_id' => $category->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'claim_date' => $validated['claim_date'],
            'currency' => $validated['currency'],
            'amount' => $validated['amount'],
            'approved_amount' => null,
            'receipt_path' => $receiptPath,
            'status' => 'pending',
            'notes' => null,
        ]);

        AuditLog::create([
            'user_id' => $currentUser->id,
            'event' => 'created',
            'action' => 'created',
            'auditable_type' => ReimbursementRequest::class,
            'auditable_id' => $reimbursement->id,
            'new_values' => $reimbursement->toArray(),
            'description' => "Pengajuan klaim reimbursement baru #{$claimNumber} oleh {$employee->full_name} sebesar {$reimbursement->currency} " . number_format($reimbursement->amount, 2),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('reimbursements.show', $reimbursement)
            ->with('success', "Klaim reimbursement {$claimNumber} berhasil diajukan dan sedang menunggu persetujuan.");
    }

    /**
     * Tampilkan Detail Klaim Reimbursement
     */
    public function show(ReimbursementRequest $reimbursement)
    {
        $this->authorizeView($reimbursement);

        $reimbursement->load(['employee.department', 'employee.branch', 'category', 'approver', 'payer']);

        return view('reimbursements.show', compact('reimbursement'));
    }

    /**
     * Form Edit Pengajuan (Hanya jika status Pending)
     */
    public function edit(ReimbursementRequest $reimbursement)
    {
        $this->authorizeEdit($reimbursement);

        if (!$reimbursement->canBeEdited()) {
            return redirect()->route('reimbursements.show', $reimbursement)
                ->with('error', 'Klaim reimbursement yang telah diproses tidak dapat diedit kembali.');
        }

        $categories = ReimbursementCategory::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('reimbursements.edit', compact('reimbursement', 'categories', 'branches'));
    }

    /**
     * Perbarui Pengajuan Reimbursement (Hanya jika status Pending)
     */
    public function update(Request $request, ReimbursementRequest $reimbursement)
    {
        $this->authorizeEdit($reimbursement);

        if (!$reimbursement->canBeEdited()) {
            return redirect()->route('reimbursements.show', $reimbursement)
                ->with('error', 'Klaim reimbursement yang telah diproses tidak dapat diubah.');
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:reimbursement_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'claim_date' => 'required|date|before_or_equal:today',
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|in:IDR,SAR',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        $category = ReimbursementCategory::findOrFail($validated['category_id']);

        // Validasi kesesuaian mata uang kategori
        if (!$category->supportsCurrency($validated['currency'])) {
            return back()->withErrors(['category_id' => "Kategori '{$category->name}' hanya berlaku untuk klaim mata uang {$category->currency}."])->withInput();
        }

        // Validasi batas maksimal nominal (plafon) jika ada
        $limit = $category->getMaxAmountForCurrency($validated['currency']);
        if ($limit !== null && $validated['amount'] > $limit) {
            $formattedLimit = format_currency($limit, $validated['currency']);
            return back()->withErrors(['amount' => "Nominal pengajuan melebihi batas maksimal kategori '{$category->name}' ({$formattedLimit})."])->withInput();
        }

        if ($request->hasFile('receipt')) {
            // Hapus file lama jika ada
            if ($reimbursement->receipt_path && Storage::disk('public')->exists($reimbursement->receipt_path)) {
                Storage::disk('public')->delete($reimbursement->receipt_path);
            }
            $reimbursement->receipt_path = $request->file('receipt')->store('reimbursements/receipts', 'public');
        } elseif ($category->requires_receipt && !$reimbursement->receipt_path) {
            return back()->withErrors(['receipt' => "Kategori '{$category->name}' mewajibkan unggah bukti struk/kuitansi."])->withInput();
        }

        $oldValues = $reimbursement->toArray();

        $reimbursement->update([
            'category_id' => $category->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'claim_date' => $validated['claim_date'],
            'currency' => $validated['currency'],
            'amount' => $validated['amount'],
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'updated',
            'action' => 'updated',
            'auditable_type' => ReimbursementRequest::class,
            'auditable_id' => $reimbursement->id,
            'old_values' => $oldValues,
            'new_values' => $reimbursement->toArray(),
            'description' => "Memperbarui data pengajuan klaim #{$reimbursement->claim_number}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('reimbursements.show', $reimbursement)
            ->with('success', "Klaim reimbursement {$reimbursement->claim_number} berhasil diperbarui.");
    }

    /**
     * Batalkan / Hapus Pengajuan (Hanya jika status Pending)
     */
    public function destroy(Request $request, ReimbursementRequest $reimbursement)
    {
        $this->authorizeEdit($reimbursement);

        if (!$reimbursement->canBeDeleted()) {
            return back()->with('error', 'Hanya pengajuan dengan status Menunggu Approval yang dapat dibatalkan.');
        }

        $claimNum = $reimbursement->claim_number;

        if ($reimbursement->receipt_path && Storage::disk('public')->exists($reimbursement->receipt_path)) {
            Storage::disk('public')->delete($reimbursement->receipt_path);
        }

        $reimbursement->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'deleted',
            'action' => 'deleted',
            'auditable_type' => ReimbursementRequest::class,
            'auditable_id' => $reimbursement->id,
            'description' => "Membatalkan/menghapus pengajuan klaim #{$claimNum}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('reimbursements.index')
            ->with('success', "Klaim reimbursement {$claimNum} telah berhasil dibatalkan.");
    }

    /**
     * Form Persetujuan Klaim (Approve)
     */
    public function approveForm(ReimbursementRequest $reimbursement)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        if (!$currentUser->canApproveReimbursement()) {
            abort(403, 'Anda tidak memiliki hak akses untuk menyetujui reimbursement.');
        }

        if (!$reimbursement->isPending()) {
            return redirect()->route('reimbursements.show', $reimbursement)
                ->with('error', 'Klaim ini sudah diproses sebelumnya.');
        }

        return view('reimbursements.approve', compact('reimbursement'));
    }

    /**
     * Persetujuan Klaim (Approve)
     */
    public function approve(Request $request, ReimbursementRequest $reimbursement)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        if (!$currentUser->canApproveReimbursement()) {
            abort(403, 'Anda tidak memiliki hak akses untuk menyetujui reimbursement.');
        }

        if (!$reimbursement->isPending()) {
            return back()->with('error', 'Klaim ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'approved_amount' => 'nullable|numeric|min:0.01|max:' . $reimbursement->amount,
            'notes' => 'nullable|string|max:1000',
        ]);

        $approvedAmount = $validated['approved_amount'] ?? $reimbursement->amount;

        $reimbursement->update([
            'status' => 'approved',
            'approved_amount' => $approvedAmount,
            'approved_by' => $currentUser->id,
            'approved_at' => now(),
            'notes' => $validated['notes'] ?? $reimbursement->notes,
        ]);

        AuditLog::create([
            'user_id' => $currentUser->id,
            'event' => 'approved',
            'action' => 'approved',
            'auditable_type' => ReimbursementRequest::class,
            'auditable_id' => $reimbursement->id,
            'description' => "Menyetujui klaim #{$reimbursement->claim_number} sebesar {$reimbursement->currency} " . number_format($approvedAmount, 2),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('reimbursements.show', $reimbursement)
            ->with('success', "Klaim reimbursement #{$reimbursement->claim_number} berhasil disetujui sebesar {$reimbursement->currency} " . number_format($approvedAmount, 2) . ". Menunggu proses pencairan oleh Finance.");
    }

    /**
     * Form Penolakan Klaim (Reject)
     */
    public function rejectForm(ReimbursementRequest $reimbursement)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        if (!$currentUser->canApproveReimbursement()) {
            abort(403, 'Anda tidak memiliki hak akses untuk menolak reimbursement.');
        }

        if (!$reimbursement->isPending()) {
            return redirect()->route('reimbursements.show', $reimbursement)
                ->with('error', 'Klaim ini sudah diproses sebelumnya.');
        }

        return view('reimbursements.reject', compact('reimbursement'));
    }

    /**
     * Tolak Klaim (Reject)
     */
    public function reject(Request $request, ReimbursementRequest $reimbursement)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        if (!$currentUser->canApproveReimbursement()) {
            abort(403, 'Anda tidak memiliki hak akses untuk menolak reimbursement.');
        }

        if (!$reimbursement->isPending()) {
            return back()->with('error', 'Klaim ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $reimbursement->update([
            'status' => 'rejected',
            'approved_by' => $currentUser->id,
            'approved_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        AuditLog::create([
            'user_id' => $currentUser->id,
            'event' => 'rejected',
            'action' => 'rejected',
            'auditable_type' => ReimbursementRequest::class,
            'auditable_id' => $reimbursement->id,
            'description' => "Menolak pengajuan klaim #{$reimbursement->claim_number} dengan alasan: {$validated['rejection_reason']}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('reimbursements.show', $reimbursement)
            ->with('success', "Klaim reimbursement #{$reimbursement->claim_number} telah ditolak.");
    }

    /**
     * Halaman Konfirmasi Pencairan Dana / Pembayaran (Finance)
     */
    public function pay(ReimbursementRequest $reimbursement)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        if (!$currentUser->canPayReimbursement()) {
            abort(403, 'Hanya bagian Finance atau Super Admin yang dapat memproses pembayaran.');
        }

        if (!$reimbursement->isApproved()) {
            return redirect()->route('reimbursements.show', $reimbursement)
                ->with('error', 'Hanya klaim yang telah Disetujui (Approved) yang dapat dicairkan.');
        }

        $reimbursement->load(['employee.department', 'employee.branch', 'category', 'approver']);

        return view('reimbursements.pay', compact('reimbursement'));
    }

    /**
     * Proses Realisasi Pembayaran Klaim (Finance)
     */
    public function processPay(Request $request, ReimbursementRequest $reimbursement)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        if (!$currentUser->canPayReimbursement()) {
            abort(403, 'Hanya bagian Finance atau Super Admin yang dapat memproses pembayaran.');
        }

        if (!$reimbursement->isApproved()) {
            return redirect()->route('reimbursements.show', $reimbursement)
                ->with('error', 'Hanya klaim berstatus Disetujui yang dapat dicairkan.');
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:transfer,cash,payroll',
            'payment_reference' => 'nullable|string|max:255',
            'payment_receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        $paymentReceiptPath = null;
        if ($request->hasFile('payment_receipt')) {
            $paymentReceiptPath = $request->file('payment_receipt')->store('reimbursements/payments', 'public');
        }

        $reimbursement->update([
            'status' => 'paid',
            'paid_by' => $currentUser->id,
            'paid_at' => now(),
            'payment_method' => $validated['payment_method'],
            'payment_reference' => $validated['payment_reference'] ?? null,
            'payment_receipt_path' => $paymentReceiptPath,
            'notes' => $validated['notes'] ? ($reimbursement->notes ? $reimbursement->notes . "\n" . $validated['notes'] : $validated['notes']) : $reimbursement->notes,
        ]);

        AuditLog::create([
            'user_id' => $currentUser->id,
            'event' => 'paid',
            'action' => 'paid',
            'auditable_type' => ReimbursementRequest::class,
            'auditable_id' => $reimbursement->id,
            'description' => "Mencairkan dana reimbursement #{$reimbursement->claim_number} sebesar {$reimbursement->currency} " . number_format($reimbursement->approved_amount ?? $reimbursement->amount, 2) . " via {$validated['payment_method']}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('reimbursements.show', $reimbursement)
            ->with('success', "Pembayaran reimbursement #{$reimbursement->claim_number} berhasil direalisasikan (Lunas).");
    }

    /**
     * Cetak Slip Bukti Reimbursement (PDF)
     */
    public function exportPdf(ReimbursementRequest $reimbursement)
    {
        $this->authorizeView($reimbursement);

        $reimbursement->load(['employee.department', 'employee.branch', 'category', 'approver', 'payer']);

        $companyName = SystemSetting::get('company_name', 'PT Layang Digital Innovation');
        $companyAddress = SystemSetting::get('company_address', 'Jl. Jenderal Sudirman No. 45, Jakarta Pusat');
        $companyPhone = SystemSetting::get('company_phone', '+62 21 555-0192');
        $printedAt = now()->translatedFormat('d F Y H:i:s');

        $financeManager = $this->resolveFinanceManager();

        $pdf = ArabicPdfHelper::loadView('reports.pdf.reimbursement_voucher', compact(
            'reimbursement',
            'companyName',
            'companyAddress',
            'companyPhone',
            'printedAt',
            'financeManager'
        ), 'a4', 'portrait');

        $filename = 'Voucher_Reimbursement_' . str_replace(['/', '\\', ' '], '_', $reimbursement->claim_number) . '.pdf';
        return $pdf->stream($filename);
    }

    /**
     * Cetak Rekapitulasi Laporan Reimbursement (PDF)
     */
    public function exportAllPdf(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        if (!$currentUser->canManageReimbursements()) {
            abort(403, 'Akses ditolak.');
        }

        $query = ReimbursementRequest::with(['employee.department', 'employee.branch', 'category', 'approver', 'payer']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('claim_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('employee', fn($eq) => $eq->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"));
            });
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }
        if ($branchId = $request->input('branch_id')) {
            $query->where('branch_id', $branchId);
        }
        if ($currency = $request->input('currency')) {
            $query->where('currency', strtoupper($currency));
        }
        if ($startDate = $request->input('start_date')) {
            $query->whereDate('claim_date', '>=', $startDate);
        }
        if ($endDate = $request->input('end_date')) {
            $query->whereDate('claim_date', '<=', $endDate);
        }

        $items = $query->orderBy('claim_date', 'desc')->get();

        $companyName = SystemSetting::get('company_name', 'PT Layang Digital Innovation');
        $companyAddress = SystemSetting::get('company_address', 'Jl. Jenderal Sudirman No. 45, Jakarta Pusat');
        $companyPhone = SystemSetting::get('company_phone', '+62 21 555-0192');
        $printedAt = now()->translatedFormat('d F Y H:i:s');
        $financeManager = $this->resolveFinanceManager();

        $pdf = ArabicPdfHelper::loadView('reports.pdf.reimbursements_summary', compact(
            'items',
            'companyName',
            'companyAddress',
            'companyPhone',
            'printedAt',
            'financeManager'
        ), 'a4', 'landscape');

        $filename = 'Rekap_Laporan_Reimbursement_' . date('Ymd_His') . '.pdf';
        return $pdf->stream($filename);
    }

    /**
     * Manajemen Master Kategori Reimbursement
     */
    public function categoriesIndex()
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        if (!$currentUser->isHrAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $categories = ReimbursementCategory::withCount('requests')->orderBy('name')->get();
        return view('reimbursements.categories.index', compact('categories'));
    }

    public function createCategory()
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        if (!$currentUser->isHrAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        return view('reimbursements.categories.create');
    }

    public function storeCategory(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        if (!$currentUser->isHrAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:reimbursement_categories,code',
            'description' => 'nullable|string|max:500',
            'currency' => 'required|in:ALL,IDR,SAR',
            'max_amount' => 'nullable|numeric|min:0',
            'max_amount_sar' => 'nullable|numeric|min:0',
            'requires_receipt' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $maxAmount = in_array($validated['currency'], ['ALL', 'IDR']) ? ($validated['max_amount'] ?? null) : null;
        $maxAmountSar = in_array($validated['currency'], ['ALL', 'SAR']) ? ($validated['max_amount_sar'] ?? null) : null;

        $category = ReimbursementCategory::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'] ?? null,
            'currency' => $validated['currency'],
            'max_amount' => $maxAmount,
            'max_amount_sar' => $maxAmountSar,
            'requires_receipt' => $request->boolean('requires_receipt', true),
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLog::create([
            'user_id' => $currentUser->id,
            'event' => 'created',
            'action' => 'created',
            'auditable_type' => ReimbursementCategory::class,
            'auditable_id' => $category->id,
            'description' => "Menambahkan master kategori reimbursement: {$category->name} ({$category->code})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('reimbursements.categories.index')
            ->with('success', "Kategori '{$category->name}' berhasil ditambahkan.");
    }

    public function editCategory(ReimbursementCategory $category)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        if (!$currentUser->isHrAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        return view('reimbursements.categories.edit', compact('category'));
    }

    public function updateCategory(Request $request, ReimbursementCategory $category)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        if (!$currentUser->isHrAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:reimbursement_categories,code,' . $category->id,
            'description' => 'nullable|string|max:500',
            'currency' => 'required|in:ALL,IDR,SAR',
            'max_amount' => 'nullable|numeric|min:0',
            'max_amount_sar' => 'nullable|numeric|min:0',
            'requires_receipt' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $maxAmount = in_array($validated['currency'], ['ALL', 'IDR']) ? ($validated['max_amount'] ?? null) : null;
        $maxAmountSar = in_array($validated['currency'], ['ALL', 'SAR']) ? ($validated['max_amount_sar'] ?? null) : null;

        $category->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'] ?? null,
            'currency' => $validated['currency'],
            'max_amount' => $maxAmount,
            'max_amount_sar' => $maxAmountSar,
            'requires_receipt' => $request->boolean('requires_receipt', true),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('reimbursements.categories.index')
            ->with('success', "Kategori '{$category->name}' berhasil diperbarui.");
    }

    public function destroyCategory(ReimbursementCategory $category)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        if (!$currentUser->isHrAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        if ($category->requests()->exists()) {
            return back()->with('error', "Kategori '{$category->name}' tidak dapat dihapus karena sudah memiliki riwayat klaim.");
        }

        $categoryName = $category->name;
        $category->delete();

        return redirect()->route('reimbursements.categories.index')
            ->with('success', "Kategori '{$categoryName}' berhasil dihapus.");
    }

    /**
     * Otorisasi Hak Akses Melihat
     */
    protected function authorizeView(ReimbursementRequest $reimbursement): void
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        if ($currentUser->canManageReimbursements()) {
            return;
        }

        if ($currentUser->isEmployee() && $reimbursement->employee_id === $currentUser->employee?->id) {
            return;
        }

        abort(403, 'Anda tidak memiliki hak akses untuk melihat data reimbursement ini.');
    }

    /**
     * Otorisasi Hak Akses Edit/Batal
     */
    protected function authorizeEdit(ReimbursementRequest $reimbursement): void
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        if ($currentUser->canManageEmployees()) {
            return;
        }

        if ($currentUser->isEmployee() && $reimbursement->employee_id === $currentUser->employee?->id) {
            return;
        }

        abort(403, 'Anda tidak memiliki hak akses untuk mengubah pengajuan reimbursement ini.');
    }

    /**
     * Ambil data Finance Manager untuk tanda tangan laporan resmi
     */
    protected function resolveFinanceManager(): array
    {
        $mgrPosition = Position::where('code', 'MGR-FIN')
            ->orWhere('title', 'like', '%Finance Manager%')
            ->first();

        if ($mgrPosition) {
            $employee = Employee::where('position_id', $mgrPosition->id)
                ->where('status', 'active')
                ->first();

            if ($employee) {
                return [
                    'name' => $employee->full_name,
                    'title' => $mgrPosition->title,
                ];
            }
        }

        return [
            'name' => 'Bambang Sudirman',
            'title' => 'Finance Manager',
        ];
    }
}

