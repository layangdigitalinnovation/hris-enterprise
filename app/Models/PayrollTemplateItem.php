<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollTemplateItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_template_id',
        'salary_component_id',
        'type',
        'calculation_mode',
        'rate_value',
        'amount',
        'is_fixed',
        'is_taxable',
        'order_index',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'rate_value' => 'decimal:2',
            'amount' => 'decimal:2',
            'is_fixed' => 'boolean',
            'is_taxable' => 'boolean',
            'order_index' => 'integer',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PayrollTemplate::class, 'payroll_template_id');
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(SalaryComponent::class, 'salary_component_id');
    }

    public function getCalculationModeLabelAttribute(): string
    {
        return match ($this->calculation_mode) {
            'fixed' => __('Nominal Tetap'),
            'percentage' => __('% dari Gaji Pokok'),
            'per_attendance' => __('Per Hari Hadir'),
            'per_late_minute' => __('Per Menit Telat'),
            default => ucfirst(str_replace('_', ' ', $this->calculation_mode)),
        };
    }

    public function getFormattedRateAttribute(): string
    {
        $curr = $this->template?->currency ?? 'IDR';
        $prefix = ($curr === 'SAR') ? 'SAR ' : 'Rp ';

        return match ($this->calculation_mode) {
            'fixed' => $prefix . number_format($this->amount > 0 ? $this->amount : $this->rate_value, 0, ',', '.'),
            'percentage' => number_format($this->rate_value, 1, ',', '.') . '% ' . __('Pokok'),
            'per_attendance' => $prefix . number_format($this->rate_value, 0, ',', '.') . ' ' . __('/ hari'),
            'per_late_minute' => $prefix . number_format($this->rate_value, 0, ',', '.') . ' ' . __('/ menit'),
            default => (string) $this->rate_value,
        };
    }
}

