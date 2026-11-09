<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLoanInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_loan_id',
        'installment_number',
        'due_date',
        'amount',
        'paid_amount',
        'paid_at',
        'payment_method',
        'payment_reference',
        'payroll_period_id',
        'status',
        'notes',
        'recorded_by',
        'proof_file',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'installment_number' => 'integer',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(EmployeeLoan::class, 'employee_loan_id');
    }

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0.0, (float) $this->amount - (float) $this->paid_amount);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'paid' => __('Lunas'),
            'partially_paid' => __('Sebagian'),
            'unpaid' => __('Belum Lunas'),
            'waived' => __('Dihapuskan'),
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassesAttribute(): string
    {
        return match ($this->status) {
            'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'partially_paid' => 'bg-blue-50 text-blue-700 border-blue-200',
            'unpaid' => 'bg-amber-50 text-amber-700 border-amber-200',
            'waived' => 'bg-slate-100 text-slate-500 border-slate-200',
            default => 'bg-slate-100 text-slate-600 border-slate-200',
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'payroll' => __('Potong Slip Gaji'),
            'bank_transfer' => __('Transfer Bank'),
            'cash' => __('Kasir / Tunai'),
            default => $this->payment_method ? ucfirst($this->payment_method) : __('Manual'),
        };
    }

    /**
     * Otomatis menandai cicilan sebagai lunas saat pengesahan payroll
     */
    public function markAsPaidByPayroll(int $periodId, ?int $actorId = null): void
    {
        $this->update([
            'paid_amount' => $this->amount,
            'paid_at' => now(),
            'payment_method' => 'payroll',
            'payroll_period_id' => $periodId,
            'recorded_by' => $actorId,
            'status' => 'paid',
            'notes' => 'Dipotong otomatis dari payroll periode #' . $periodId,
        ]);

        if ($this->loan && $this->loan->remaining_amount <= 0.01) {
            $this->loan->update(['status' => 'completed']);
        }
    }
}
