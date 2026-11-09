<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class EmployeeLoan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'loan_number',
        'employee_id',
        'branch_id',
        'currency',
        'amount',
        'approved_amount',
        'tenor_months',
        'interest_rate_percent',
        'monthly_installment',
        'repayment_start_date',
        'reason',
        'payroll_deduction',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejection_reason',
        'disbursed_by',
        'disbursed_at',
        'payment_method',
        'disbursement_reference',
        'disbursement_proof',
        'disbursement_notes',
        'attachment_file',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'approved_amount' => 'decimal:2',
            'interest_rate_percent' => 'decimal:2',
            'monthly_installment' => 'decimal:2',
            'tenor_months' => 'integer',
            'payroll_deduction' => 'boolean',
            'repayment_start_date' => 'date',
            'approved_at' => 'datetime',
            'disbursed_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function disburser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(EmployeeLoanInstallment::class, 'employee_loan_id')->orderBy('installment_number');
    }

    public function getEffectivePrincipalAttribute(): float
    {
        return (float) ($this->approved_amount ?? $this->amount);
    }

    public function getCurrencySymbolAttribute(): string
    {
        return $this->currency === 'SAR' ? 'SAR' : 'Rp';
    }

    public function getTotalRepaidAmountAttribute(): float
    {
        return (float) $this->installments()->sum('paid_amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0.0, round($this->effective_principal - $this->total_repaid_amount, 2));
    }

    public function getPaidInstallmentsCountAttribute(): int
    {
        return $this->installments()->where('status', 'paid')->count();
    }

    public function getRepaymentProgressPercentageAttribute(): float
    {
        $principal = $this->effective_principal;
        if ($principal <= 0) return 0.0;
        return round(min(100.0, ($this->total_repaid_amount / $principal) * 100), 1);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => __('Menunggu Approval'),
            'approved' => __('Disetujui (Siap Cair)'),
            'active', 'disbursed' => __('Aktif Berjalan'),
            'completed' => __('Lunas'),
            'rejected' => __('Ditolak'),
            'cancelled' => __('Dibatalkan'),
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassesAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-amber-50 text-amber-700 border-amber-200/80',
            'approved' => 'bg-blue-50 text-blue-700 border-blue-200/80',
            'active', 'disbursed' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
            'completed' => 'bg-purple-50 text-purple-700 border-purple-200/80',
            'rejected' => 'bg-rose-50 text-rose-700 border-rose-200/80',
            default => 'bg-slate-100 text-slate-600 border-slate-200',
        };
    }

    /**
     * Generate format nomor pinjaman unik
     * Contoh: LOAN-202611-0001
     */
    public static function generateLoanNumber(): string
    {
        $prefix = 'LOAN-' . date('Ym') . '-';
        $lastLoan = self::withTrashed()
            ->where('loan_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $seq = 1;
        if ($lastLoan) {
            $lastSeq = (int) substr($lastLoan->loan_number, -4);
            $seq = $lastSeq + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate jadwal angsuran (Installments) otomatis
     */
    public function generateInstallments(): void
    {
        $this->installments()->delete();

        $tenor = max(1, (int) $this->tenor_months);
        $principal = (float) $this->effective_principal;
        $monthly = round($principal / $tenor, 2);
        $startDate = Carbon::parse($this->repayment_start_date);
        $accumulated = 0.0;

        for ($i = 1; $i <= $tenor; $i++) {
            $amt = ($i === $tenor) ? round($principal - $accumulated, 2) : $monthly;
            $accumulated += $monthly;
            $dueDate = (clone $startDate)->addMonthsNoOverflow($i - 1);

            $this->installments()->create([
                'installment_number' => $i,
                'due_date' => $dueDate->format('Y-m-d'),
                'amount' => $amt,
                'paid_amount' => 0.00,
                'paid_at' => null,
                'payment_method' => $this->payroll_deduction ? 'payroll' : 'bank_transfer',
                'status' => 'unpaid',
            ]);
        }
    }

    /**
     * Catat pembayaran angsuran pinjaman (1 cicilan atau pelunasan sisa)
     */
    public function recordRepayment(
        float $repayAmount,
        string $paymentMethod = 'bank_transfer',
        ?string $paidDate = null,
        ?int $recordedBy = null,
        ?string $reference = null,
        ?string $proofPath = null,
        ?string $notes = null,
        ?int $payrollPeriodId = null
    ): void {
        $remainingToAllocate = $repayAmount;
        $dateObj = $paidDate ? Carbon::parse($paidDate) : now();

        $unpaidInstallments = $this->installments()
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->orderBy('installment_number')
            ->get();

        foreach ($unpaidInstallments as $inst) {
            if ($remainingToAllocate <= 0) break;

            $neededForInst = (float) $inst->amount - (float) $inst->paid_amount;

            if ($remainingToAllocate >= $neededForInst) {
                $inst->update([
                    'paid_amount' => $inst->amount,
                    'paid_at' => $dateObj,
                    'payment_method' => $paymentMethod,
                    'payment_reference' => $reference,
                    'payroll_period_id' => $payrollPeriodId,
                    'status' => 'paid',
                    'notes' => $notes,
                    'recorded_by' => $recordedBy,
                    'proof_file' => $proofPath ?? $inst->proof_file,
                ]);
                $remainingToAllocate -= $neededForInst;
            } else {
                $inst->update([
                    'paid_amount' => (float) $inst->paid_amount + $remainingToAllocate,
                    'paid_at' => $dateObj,
                    'payment_method' => $paymentMethod,
                    'payment_reference' => $reference,
                    'payroll_period_id' => $payrollPeriodId,
                    'status' => 'partially_paid',
                    'notes' => $notes,
                    'recorded_by' => $recordedBy,
                    'proof_file' => $proofPath ?? $inst->proof_file,
                ]);
                $remainingToAllocate = 0.0;
            }
        }

        // Jika seluruh pokok sudah lunas, ubah status pinjaman menjadi completed
        if ($this->remaining_amount <= 0.01) {
            $this->update(['status' => 'completed']);
        }
    }
}
