<?php

namespace App\Models;

use App\Helpers\CurrencyHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Investor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'branch_id',
        'investor_code',
        'name',
        'nik_or_passport',
        'email',
        'phone',
        'bank_name',
        'bank_account_no',
        'bank_account_holder',
        'currency',
        'initial_investment',
        'investment_date',
        'contract_end_date',
        'profit_share_percentage',
        'profit_share_scheme',
        'has_fixed_stipend',
        'fixed_stipend_amount',
        'stipend_payment_day',
        'include_stipend_in_bep',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'initial_investment' => 'decimal:2',
            'profit_share_percentage' => 'decimal:2',
            'fixed_stipend_amount' => 'decimal:2',
            'has_fixed_stipend' => 'boolean',
            'include_stipend_in_bep' => 'boolean',
            'investment_date' => 'date',
            'contract_end_date' => 'date',
            'stipend_payment_day' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(InvestorDistribution::class)->orderByDesc('distribution_date')->orderByDesc('id');
    }

    public function profitShareDistributions(): HasMany
    {
        return $this->hasMany(InvestorDistribution::class)->where('type', 'profit_share')->orderByDesc('distribution_date');
    }

    public function stipendDistributions(): HasMany
    {
        return $this->hasMany(InvestorDistribution::class)->where('type', 'stipend')->orderByDesc('distribution_date');
    }

    public function capitalReturnDistributions(): HasMany
    {
        return $this->hasMany(InvestorDistribution::class)->where('type', 'capital_return')->orderByDesc('distribution_date');
    }

    // Calculations & Metrics
    public function getTotalProfitSharePaidAttribute(): float
    {
        return (float) $this->distributions()
            ->where('type', 'profit_share')
            ->where('status', 'paid')
            ->sum('amount');
    }

    public function getTotalStipendPaidAttribute(): float
    {
        return (float) $this->distributions()
            ->where('type', 'stipend')
            ->where('status', 'paid')
            ->sum('amount');
    }

    public function getTotalCapitalReturnPaidAttribute(): float
    {
        return (float) $this->distributions()
            ->where('type', 'capital_return')
            ->where('status', 'paid')
            ->sum('amount');
    }

    public function getTotalDistributionsPaidAttribute(): float
    {
        return (float) $this->distributions()
            ->where('status', 'paid')
            ->sum('amount');
    }

    public function getTotalPendingDistributionsAttribute(): float
    {
        return (float) $this->distributions()
            ->whereIn('status', ['pending', 'approved'])
            ->sum('amount');
    }

    public function getTotalReturnForBepAttribute(): float
    {
        if ($this->include_stipend_in_bep) {
            return $this->total_profit_share_paid + $this->total_capital_return_paid + $this->total_stipend_paid;
        }
        return $this->total_profit_share_paid + $this->total_capital_return_paid;
    }

    public function getBepRemainingAttribute(): float
    {
        $rem = (float) $this->initial_investment - $this->total_return_for_bep;
        return max(0, $rem);
    }

    public function getBepProgressPercentageAttribute(): float
    {
        $invested = (float) $this->initial_investment;
        if ($invested <= 0) {
            return 100.0;
        }
        $ratio = ($this->total_return_for_bep / $invested) * 100;
        return min(100.0, round($ratio, 2));
    }

    public function getIsBepAchievedAttribute(): bool
    {
        return $this->total_return_for_bep >= (float) $this->initial_investment;
    }

    public function getRoiPercentageAttribute(): float
    {
        $invested = (float) $this->initial_investment;
        if ($invested <= 0) {
            return 0.0;
        }
        $totalReceived = $this->total_distributions_paid;
        return round((($totalReceived - $invested) / $invested) * 100, 2);
    }

    public function getNetProfitReceivedAttribute(): float
    {
        $invested = (float) $this->initial_investment;
        $totalReceived = $this->total_distributions_paid;
        return max(0, $totalReceived - $invested);
    }

    /**
     * Estimasi sisa bulan menuju BEP berdasarkan rata-rata dividen/bulan yang dibayar
     */
    public function getEstimatedMonthsToBepAttribute(): ?int
    {
        if ($this->is_bep_achieved) {
            return 0;
        }

        $paidDistributions = $this->distributions()->where('status', 'paid')->get();
        if ($paidDistributions->isEmpty()) {
            return null;
        }

        $avgReturn = $paidDistributions->avg('amount');
        if ($avgReturn <= 0) {
            return null;
        }

        return (int) ceil($this->bep_remaining / $avgReturn);
    }

    public static function generateCode(): string
    {
        $year = date('Y');
        $prefix = "INV-{$year}-";
        $last = self::withTrashed()
            ->where('investor_code', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('investor_code');

        $num = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $m)) {
            $num = (int) $m[1] + 1;
        }

        return $prefix . str_pad((string) $num, 3, '0', STR_PAD_LEFT);
    }
}

