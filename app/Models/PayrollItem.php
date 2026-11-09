<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PayrollItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_period_id',
        'employee_id',
        'currency',
        'basic_salary',
        'total_allowances',
        'total_overtime',
        'total_deductions',
        'net_salary',
        'details',
        'status',
    ];

    /**
     * Get item currency (IDR or SAR).
     */
    public function getCurrencyAttribute($value): string
    {
        if (!empty($value)) {
            return strtoupper($value);
        }
        if (!empty($this->details['currency'])) {
            return strtoupper($this->details['currency']);
        }
        if ($this->employee) {
            return $this->employee->effective_currency;
        }
        return 'IDR';
    }

    /**
     * Format a monetary value according to this item's currency.
     */
    public function formatAmount(?float $amount, bool $withSymbol = true): string
    {
        return \App\Helpers\CurrencyHelper::format((float) $amount, $this->currency, $withSymbol);
    }

    protected function casts(): array
    {
        return [
            'basic_salary' => 'decimal:2',
            'total_allowances' => 'decimal:2',
            'total_overtime' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'details' => 'array',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function payrollPeriod(): BelongsTo
    {
        return $this->period();
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function payslip(): HasOne
    {
        return $this->hasOne(Payslip::class);
    }
}
