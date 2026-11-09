<?php

namespace App\Http\Controllers;

use App\Helpers\CurrencyHelper;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Investor;
use App\Models\InvestorDistribution;
use App\Models\SystemSetting;
use App\Models\User;
use App\Helpers\ArabicPdfHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class InvestorController extends Controller
{
    /**
     * Dashboard & Daftar Investor
     */
    public function index(Request $request)
    {
        $query = Investor::with(['branch', 'user', 'distributions']);

        // Filter Pencarian
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('investor_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('nik_or_passport', 'like', "%{$search}%");
            });
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

        // Filter Status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Pagination Per Halaman (Default 6 agar tidak menumpuk ke bawah, bisa dipilih 3, 6, 9, 12, 24)
        $perPage = (int) $request->input('per_page', 6);
        if (!in_array($perPage, [3, 6, 9, 12, 24])) {
            $perPage = 6;
        }

        $investors = $query->orderBy('name')->paginate($perPage)->withQueryString();

        // Metrik Ringkasan Finansial
        $allActive = Investor::with('distributions')->where('status', 'active')->get();

        // Modal Tertanam
        $totalCapitalIdr = $allActive->where('currency', 'IDR')->sum('initial_investment');
        $totalCapitalSar = $allActive->where('currency', 'SAR')->sum('initial_investment');

        // Total Bagi Hasil Terbayar
        $totalProfitShareIdr = InvestorDistribution::where('type', 'profit_share')
            ->where('status', 'paid')
            ->where('currency', 'IDR')
            ->sum('amount');
        $totalProfitShareSar = InvestorDistribution::where('type', 'profit_share')
            ->where('status', 'paid')
            ->where('currency', 'SAR')
            ->sum('amount');

        // Total Gaji Khusus Terbayar
        $totalStipendIdr = InvestorDistribution::where('type', 'stipend')
            ->where('status', 'paid')
            ->where('currency', 'IDR')
            ->sum('amount');
        $totalStipendSar = InvestorDistribution::where('type', 'stipend')
            ->where('status', 'paid')
            ->where('currency', 'SAR')
            ->sum('amount');

        // Rata-rata Capaian BEP
        $bepPercentages = $allActive->map(fn($inv) => $inv->bep_progress_percentage);
        $avgBepProgress = $bepPercentages->count() > 0 ? round($bepPercentages->avg(), 1) : 0;

        $branches = Branch::orderBy('name')->get();

        return view('investors.index', compact(
            'investors',
            'branches',
            'totalCapitalIdr',
            'totalCapitalSar',
            'totalProfitShareIdr',
            'totalProfitShareSar',
            'totalStipendIdr',
            'totalStipendSar',
            'avgBepProgress'
        ));
    }

    /**
     * Form Pendaftaran Investor Baru
     */
    public function create()
    {
        $branches = Branch::orderBy('name')->get();
        $suggestedCode = Investor::generateCode();

        return view('investors.create', compact('branches', 'suggestedCode'));
    }

    /**
     * Simpan Investor Baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'investor_code' => 'required|string|max:50|unique:investors,investor_code',
            'name' => 'required|string|max:255',
            'nik_or_passport' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'branch_id' => 'nullable|exists:branches,id',
            'currency' => 'required|in:IDR,SAR',
            'initial_investment' => 'required|numeric|min:0',
            'investment_date' => 'required|date',
            'contract_end_date' => 'nullable|date|after_or_equal:investment_date',
            'profit_share_percentage' => 'required|numeric|min:0|max:100',
            'profit_share_scheme' => 'required|in:net_profit,gross_revenue,fixed_monthly',
            'has_fixed_stipend' => 'nullable|boolean',
            'fixed_stipend_amount' => 'nullable|numeric|min:0',
            'stipend_payment_day' => 'nullable|integer|min:1|max:31',
            'include_stipend_in_bep' => 'nullable|boolean',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_no' => 'nullable|string|max:100',
            'bank_account_holder' => 'nullable|string|max:255',
            'status' => 'required|in:active,completed,paused',
            'notes' => 'nullable|string|max:1000',

            // Akun Login Portal Investor
            'create_user_account' => 'nullable|boolean',
            'user_email' => 'nullable|required_if:create_user_account,1|email|unique:users,email',
            'user_password' => 'nullable|required_if:create_user_account,1|string|min:6',
        ]);

        DB::beginTransaction();
        try {
            $userId = null;

            if ($request->boolean('create_user_account') && !empty($validated['user_email'])) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['user_email'],
                    'password' => Hash::make($validated['user_password'] ?? 'investor12345'),
                    'role' => 'investor',
                    'phone' => $validated['phone'] ?? null,
                    'is_active' => true,
                ]);
                $userId = $user->id;
            }

            $investor = Investor::create([
                'user_id' => $userId,
                'branch_id' => $validated['branch_id'] ?? null,
                'investor_code' => strtoupper($validated['investor_code']),
                'name' => $validated['name'],
                'nik_or_passport' => $validated['nik_or_passport'] ?? null,
                'email' => $validated['email'] ?? ($validated['user_email'] ?? null),
                'phone' => $validated['phone'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'bank_account_no' => $validated['bank_account_no'] ?? null,
                'bank_account_holder' => $validated['bank_account_holder'] ?? null,
                'currency' => strtoupper($validated['currency']),
                'initial_investment' => $validated['initial_investment'],
                'investment_date' => $validated['investment_date'],
                'contract_end_date' => $validated['contract_end_date'] ?? null,
                'profit_share_percentage' => $validated['profit_share_percentage'],
                'profit_share_scheme' => $validated['profit_share_scheme'],
                'has_fixed_stipend' => $request->boolean('has_fixed_stipend'),
                'fixed_stipend_amount' => $request->boolean('has_fixed_stipend') ? ($validated['fixed_stipend_amount'] ?? 0) : 0,
                'stipend_payment_day' => $validated['stipend_payment_day'] ?? 25,
                'include_stipend_in_bep' => $request->boolean('include_stipend_in_bep'),
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'event' => 'created',
                'action' => 'created',
                'auditable_type' => Investor::class,
                'auditable_id' => $investor->id,
                'new_values' => $investor->toArray(),
                'description' => "Menambahkan data investor baru: {$investor->name} ({$investor->investor_code}) dengan modal {$investor->currency} " . number_format($investor->initial_investment, 2),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('investors.show', $investor)
                ->with('success', "Investor {$investor->name} ({$investor->investor_code}) berhasil didaftarkan.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan investor: ' . $e->getMessage());
        }
    }

    /**
     * Detail Investor & Portofolio BEP
     */
    public function show(Investor $investor)
    {
        $investor->load([
            'branch',
            'user',
            'creator',
            'distributions.creator',
            'distributions.approver'
        ]);

        return view('investors.show', compact('investor'));
    }

    /**
     * Edit Investor
     */
    public function edit(Investor $investor)
    {
        $branches = Branch::orderBy('name')->get();
        return view('investors.edit', compact('investor', 'branches'));
    }

    /**
     * Perbarui Data Investor
     */
    public function update(Request $request, Investor $investor)
    {
        $validated = $request->validate([
            'investor_code' => ['required', 'string', 'max:50', Rule::unique('investors')->ignore($investor->id)],
            'name' => 'required|string|max:255',
            'nik_or_passport' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'branch_id' => 'nullable|exists:branches,id',
            'currency' => 'required|in:IDR,SAR',
            'initial_investment' => 'required|numeric|min:0',
            'investment_date' => 'required|date',
            'contract_end_date' => 'nullable|date|after_or_equal:investment_date',
            'profit_share_percentage' => 'required|numeric|min:0|max:100',
            'profit_share_scheme' => 'required|in:net_profit,gross_revenue,fixed_monthly',
            'has_fixed_stipend' => 'nullable|boolean',
            'fixed_stipend_amount' => 'nullable|numeric|min:0',
            'stipend_payment_day' => 'nullable|integer|min:1|max:31',
            'include_stipend_in_bep' => 'nullable|boolean',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_no' => 'nullable|string|max:100',
            'bank_account_holder' => 'nullable|string|max:255',
            'status' => 'required|in:active,completed,paused',
            'notes' => 'nullable|string|max:1000',
        ]);

        $oldValues = $investor->toArray();

        $investor->update([
            'branch_id' => $validated['branch_id'] ?? null,
            'investor_code' => strtoupper($validated['investor_code']),
            'name' => $validated['name'],
            'nik_or_passport' => $validated['nik_or_passport'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_no' => $validated['bank_account_no'] ?? null,
            'bank_account_holder' => $validated['bank_account_holder'] ?? null,
            'currency' => strtoupper($validated['currency']),
            'initial_investment' => $validated['initial_investment'],
            'investment_date' => $validated['investment_date'],
            'contract_end_date' => $validated['contract_end_date'] ?? null,
            'profit_share_percentage' => $validated['profit_share_percentage'],
            'profit_share_scheme' => $validated['profit_share_scheme'],
            'has_fixed_stipend' => $request->boolean('has_fixed_stipend'),
            'fixed_stipend_amount' => $request->boolean('has_fixed_stipend') ? ($validated['fixed_stipend_amount'] ?? 0) : 0,
            'stipend_payment_day' => $validated['stipend_payment_day'] ?? 25,
            'include_stipend_in_bep' => $request->boolean('include_stipend_in_bep'),
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Sinkronisasi data ke Akun Portal Login Investor
        if ($investor->user) {
            $userUpdates = [
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
            ];
            if (!empty($validated['email'])) {
                $userUpdates['email'] = $validated['email'];
            }
            if (!empty($request->input('reset_portal_password'))) {
                $userUpdates['password'] = Hash::make($request->input('reset_portal_password'));
            }
            $investor->user->update($userUpdates);
        } elseif ($request->boolean('create_user_account') && !empty($request->input('user_email'))) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $request->input('user_email'),
                'password' => Hash::make($request->input('user_password') ?? 'investor12345'),
                'role' => 'investor',
                'phone' => $validated['phone'] ?? null,
                'is_active' => true,
            ]);
            $investor->update(['user_id' => $user->id]);
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'updated',
            'action' => 'updated',
            'auditable_type' => Investor::class,
            'auditable_id' => $investor->id,
            'old_values' => $oldValues,
            'new_values' => $investor->fresh()->toArray(),
            'description' => "Memperbarui profil data investor: {$investor->name} ({$investor->investor_code})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('investors.show', $investor)
            ->with('success', "Data investor {$investor->name} berhasil diperbarui.");
    }

    /**
     * Hapus Investor
     */
    public function destroy(Investor $investor)
    {
        $name = $investor->name;
        $code = $investor->investor_code;

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'deleted',
            'action' => 'deleted',
            'auditable_type' => Investor::class,
            'auditable_id' => $investor->id,
            'old_values' => $investor->toArray(),
            'description' => "Menghapus investor: {$name} ({$code})",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $investor->delete();

        return redirect()->route('investors.index')
            ->with('success', "Investor {$name} ({$code}) berhasil dihapus.");
    }

    /**
     * Formulir Halaman Khusus Catat Distribusi Baru (Dedicated Page)
     */
    public function createDistribution(Investor $investor)
    {
        $suggestedNumber = InvestorDistribution::generateNumber();
        return view('investors.distributions.create', compact('investor', 'suggestedNumber'));
    }

    /**
     * Simpan Transaksi Distribusi Baru
     */
    public function storeDistribution(Request $request, Investor $investor)
    {
        $validated = $request->validate([
            'distribution_number' => 'nullable|string|max:50|unique:investor_distributions,distribution_number',
            'type' => 'required|in:profit_share,stipend,capital_return',
            'period_label' => 'required|string|max:60',
            'distribution_date' => 'required|date',
            'basis_amount' => 'nullable|numeric|min:0',
            'percentage_applied' => 'nullable|numeric|min:0|max:100',
            'amount' => 'required|numeric|min:0.01',
            'status' => 'required|in:pending,approved,paid',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string|max:50',
            'payment_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $distNumber = !empty($validated['distribution_number'])
            ? strtoupper($validated['distribution_number'])
            : InvestorDistribution::generateNumber();

        $distribution = InvestorDistribution::create([
            'investor_id' => $investor->id,
            'distribution_number' => $distNumber,
            'type' => $validated['type'],
            'period_label' => $validated['period_label'],
            'distribution_date' => $validated['distribution_date'],
            'currency' => $investor->currency,
            'basis_amount' => $validated['basis_amount'] ?? null,
            'percentage_applied' => $validated['percentage_applied'] ?? null,
            'amount' => $validated['amount'],
            'status' => $validated['status'],
            'payment_date' => $validated['status'] === 'paid' ? ($validated['payment_date'] ?? now()->toDateString()) : $validated['payment_date'],
            'payment_method' => $validated['payment_method'] ?? 'bank_transfer',
            'payment_reference' => $validated['payment_reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
            'approved_by' => in_array($validated['status'], ['approved', 'paid']) ? auth()->id() : null,
        ]);

        $typeLabel = $distribution->type_label;

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'created',
            'action' => 'created',
            'auditable_type' => InvestorDistribution::class,
            'auditable_id' => $distribution->id,
            'new_values' => $distribution->toArray(),
            'description' => "Mencatat {$typeLabel} untuk investor {$investor->name} ({$investor->investor_code}) periode {$distribution->period_label} sebesar {$investor->currency} " . number_format($distribution->amount, 2),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('investors.show', $investor)
            ->with('success', "Transaksi {$typeLabel} {$distNumber} berhasil dicatat.");
    }

    /**
     * Formulir Halaman Khusus Edit Distribusi (Dedicated Page)
     */
    public function editDistribution(InvestorDistribution $distribution)
    {
        $investor = $distribution->investor;
        return view('investors.distributions.edit', compact('distribution', 'investor'));
    }

    /**
     * Update Data Distribusi
     */
    public function updateDistribution(Request $request, InvestorDistribution $distribution)
    {
        $investor = $distribution->investor;

        $validated = $request->validate([
            'distribution_number' => ['required', 'string', 'max:50', Rule::unique('investor_distributions')->ignore($distribution->id)],
            'type' => 'required|in:profit_share,stipend,capital_return',
            'period_label' => 'required|string|max:60',
            'distribution_date' => 'required|date',
            'basis_amount' => 'nullable|numeric|min:0',
            'percentage_applied' => 'nullable|numeric|min:0|max:100',
            'amount' => 'required|numeric|min:0.01',
            'status' => 'required|in:pending,approved,paid,cancelled',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string|max:50',
            'payment_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $oldValues = $distribution->toArray();

        $distribution->update([
            'distribution_number' => strtoupper($validated['distribution_number']),
            'type' => $validated['type'],
            'period_label' => $validated['period_label'],
            'distribution_date' => $validated['distribution_date'],
            'basis_amount' => $validated['basis_amount'] ?? null,
            'percentage_applied' => $validated['percentage_applied'] ?? null,
            'amount' => $validated['amount'],
            'status' => $validated['status'],
            'payment_date' => $validated['status'] === 'paid' ? ($validated['payment_date'] ?? ($distribution->payment_date ?? now()->toDateString())) : $validated['payment_date'],
            'payment_method' => $validated['payment_method'] ?? 'bank_transfer',
            'payment_reference' => $validated['payment_reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'approved_by' => in_array($validated['status'], ['approved', 'paid']) ? ($distribution->approved_by ?? auth()->id()) : null,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'updated',
            'action' => 'updated',
            'auditable_type' => InvestorDistribution::class,
            'auditable_id' => $distribution->id,
            'old_values' => $oldValues,
            'new_values' => $distribution->fresh()->toArray(),
            'description' => "Memperbarui transaksi {$distribution->distribution_number} milik investor {$investor->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('investors.show', $investor)
            ->with('success', "Transaksi {$distribution->distribution_number} berhasil diperbarui.");
    }

    /**
     * Formulir Halaman Khusus Pembayaran / Bayar Sekarang (Dedicated Page)
     */
    public function payDistributionForm(InvestorDistribution $distribution)
    {
        $investor = $distribution->investor;
        return view('investors.distributions.pay', compact('distribution', 'investor'));
    }

    /**
     * Proses Konfirmasi Pembayaran
     */
    public function processPayment(Request $request, InvestorDistribution $distribution)
    {
        $investor = $distribution->investor;

        $validated = $request->validate([
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|max:50',
            'payment_reference' => 'nullable|string|max:100',
        ]);

        $distribution->update([
            'status' => 'paid',
            'payment_date' => $validated['payment_date'],
            'payment_method' => $validated['payment_method'],
            'payment_reference' => $validated['payment_reference'] ?? null,
            'approved_by' => auth()->id(),
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'updated',
            'action' => 'updated',
            'auditable_type' => InvestorDistribution::class,
            'auditable_id' => $distribution->id,
            'description' => "Pembayaran transaksi {$distribution->distribution_number} sebesar {$distribution->currency} " . number_format($distribution->amount, 2) . " telah dikonfirmasi lunas oleh " . auth()->user()->name,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('investors.show', $investor)
            ->with('success', "Pembayaran transaksi {$distribution->distribution_number} berhasil dikonfirmasi lunas.");
    }

    /**
     * Hapus Catatan Distribusi
     */
    public function destroyDistribution(InvestorDistribution $distribution)
    {
        $investor = $distribution->investor;
        $distNo = $distribution->distribution_number;

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'deleted',
            'action' => 'deleted',
            'auditable_type' => InvestorDistribution::class,
            'auditable_id' => $distribution->id,
            'old_values' => $distribution->toArray(),
            'description' => "Menghapus catatan distribusi {$distNo} milik {$investor->name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $distribution->delete();

        return redirect()->route('investors.show', $investor)
            ->with('success', "Transaksi {$distNo} berhasil dihapus.");
    }

    /**
     * Cetak Portofolio Investor Lengkap (PDF)
     */
    public function exportPdf(Investor $investor)
    {
        $companyName = SystemSetting::get('company_name', 'PT Layang Digital Innovation');
        $companyAddress = SystemSetting::get('company_address', 'Jl. Jenderal Sudirman No. 45, Jakarta Pusat');
        $companyPhone = SystemSetting::get('company_phone', '+62 21 555-0192');
        $printedAt = now()->translatedFormat('d F Y H:i:s');

        $financeManager = $this->resolveFinanceManager();
        $financeManagerName = $financeManager['name'];
        $financeManagerTitle = $financeManager['title'];

        $investor->load(['branch', 'distributions' => fn($q) => $q->orderByDesc('distribution_date')]);

        $pdf = ArabicPdfHelper::loadView('reports.pdf.investor_portfolio', compact(
            'investor',
            'companyName',
            'companyAddress',
            'companyPhone',
            'printedAt',
            'financeManagerName',
            'financeManagerTitle'
        ), 'a4', 'portrait');

        $filename = 'Portofolio_Investor_' . str_replace(['/', '\\', ' '], '_', $investor->investor_code) . '_' . date('Ymd_His') . '.pdf';
        return $pdf->stream($filename);
    }

    /**
     * Cetak Rekapitulasi Eksekutif Portofolio Seluruh Investor (PDF)
     */
    public function exportAllPdf(Request $request)
    {
        $companyName = SystemSetting::get('company_name', 'PT Layang Digital Innovation');
        $companyAddress = SystemSetting::get('company_address', 'Jl. Jenderal Sudirman No. 45, Jakarta Pusat');
        $companyPhone = SystemSetting::get('company_phone', '+62 21 555-0192');
        $printedAt = now()->translatedFormat('d F Y H:i:s');

        $financeManager = $this->resolveFinanceManager();
        $financeManagerName = $financeManager['name'];
        $financeManagerTitle = $financeManager['title'];

        $investors = Investor::with(['branch', 'distributions'])->orderBy('name')->get();

        $totalCapitalIdr = $investors->where('currency', 'IDR')->sum('initial_investment');
        $totalCapitalSar = $investors->where('currency', 'SAR')->sum('initial_investment');

        $totalProfitShareIdr = InvestorDistribution::where('type', 'profit_share')
            ->where('status', 'paid')
            ->where('currency', 'IDR')
            ->sum('amount');
        $totalProfitShareSar = InvestorDistribution::where('type', 'profit_share')
            ->where('status', 'paid')
            ->where('currency', 'SAR')
            ->sum('amount');

        $bepPercentages = $investors->map(fn($inv) => $inv->bep_progress_percentage);
        $avgBepProgress = $bepPercentages->count() > 0 ? round($bepPercentages->avg(), 1) : 0;

        $pdf = ArabicPdfHelper::loadView('reports.pdf.investors_summary', compact(
            'investors',
            'totalCapitalIdr',
            'totalCapitalSar',
            'totalProfitShareIdr',
            'totalProfitShareSar',
            'avgBepProgress',
            'companyName',
            'companyAddress',
            'companyPhone',
            'printedAt',
            'financeManagerName',
            'financeManagerTitle'
        ), 'a4', 'landscape');

        $filename = 'Rekap_Eksekutif_Portofolio_Investor_' . date('Ymd_His') . '.pdf';
        return $pdf->stream($filename);
    }

    /**
     * Cari profil Finance Manager secara otomatis dari database
     */
    protected function resolveFinanceManager(): array
    {
        // 1. Cek Employee dengan posisi Finance Manager (MGR-FIN atau title Finance Manager)
        $emp = Employee::whereHas('position', function ($q) {
            $q->where('code', 'MGR-FIN')
                ->orWhere('title', 'like', '%Finance Manager%')
                ->orWhere('title', 'like', '%Manajer Keuangan%');
        })->where('status', 'active')->with(['user', 'position'])->first();

        // 2. Jika tidak ditemukan, cek Employee di Departemen Finance yang memiliki user role manager
        if (!$emp) {
            $emp = Employee::whereHas('department', function ($q) {
                $q->where('code', 'FIN')->orWhere('name', 'like', '%Finance%');
            })->whereHas('user', function ($q) {
                $q->where('role', 'manager');
            })->where('status', 'active')->with(['user', 'position'])->first();
        }

        // 3. Jika tidak ditemukan, cek User dengan role finance
        if (!$emp) {
            $financeUser = User::where('role', 'finance')->where('is_active', true)->with('employee.position')->first();
            if ($financeUser && $financeUser->employee) {
                $emp = $financeUser->employee;
            }
        }

        // 4. Cek SystemSetting jika disetel khusus secara override
        $settingName = SystemSetting::get('finance_manager_name');
        if (!empty($settingName)) {
            return [
                'name' => $settingName,
                'title' => SystemSetting::get('finance_manager_title', 'Finance Manager'),
            ];
        }

        if ($emp) {
            $name = !empty(trim($emp->full_name)) 
                ? $emp->full_name 
                : ($emp->user ? preg_replace('/\s*\(.*?\)/', '', $emp->user->name) : 'Finance Manager');

            $title = $emp->position?->title ?? 'Finance Manager';

            return [
                'name' => $name,
                'title' => $title,
            ];
        }

        // 5. Fallback ke Bambang Sudirman (Finance Manager bawaan)
        return [
            'name' => 'Bambang Sudirman',
            'title' => 'Finance Manager',
        ];
    }

    /**
     * Portal Mandiri Investor (Self-Service Investor)
     */
    public function portal(Request $request)
    {
        $user = auth()->user();

        // Jika user adalah investor, ambil investor miliknya
        if ($user->isInvestor()) {
            $investor = Investor::with(['branch', 'distributions'])->where('user_id', $user->id)->first();
        } else {
            // Admin sedang pratinjau portal investor
            $investorId = $request->input('investor_id');
            if ($investorId) {
                $investor = Investor::with(['branch', 'distributions'])->find($investorId);
            } else {
                $investor = Investor::with(['branch', 'distributions'])->first();
            }
        }

        if (!$investor) {
            return view('investors.portal_empty');
        }

        return view('investors.portal', compact('investor'));
    }
}
