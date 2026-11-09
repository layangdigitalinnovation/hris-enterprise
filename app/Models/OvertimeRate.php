<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OvertimeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'day_type',
        'calculation_type',
        'currency',
        'rate_per_hour',
        'multiplier',
        'is_default',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'rate_per_hour' => 'decimal:2',
            'multiplier' => 'decimal:2',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function overtimeRequests()
    {
        return $this->hasMany(OvertimeRequest::class);
    }

    /**
     * Get human-readable rate label
     */
    public function getRateLabelAttribute(): string
    {
        $curr = $this->currency ?? 'IDR';
        $prefix = ($curr === 'SAR') ? 'SAR ' : 'Rp ';

        if ($this->calculation_type === 'saudi_article_107') {
            return __('Pasal 107 UU Arab Saudi (Upah/Jam + 50% Gaji Pokok/Jam)');
        }

        if ($this->calculation_type === 'depnaker_formula') {
            return "Formula Depnaker (1/173 × Gaji × {$this->multiplier}x)";
        }

        if ($this->calculation_type === 'percentage') {
            return "Persentase Gaji ({$this->multiplier}% / Jam)";
        }

        return $prefix . number_format($this->rate_per_hour, 0, ',', '.') . " / Jam";
    }

    /**
     * Calculate overtime pay for given hours, basic salary, and fixed allowances.
     */
    public function calculatePay(float $hours, float $basicSalary = 0, float $fixedAllowances = 0, ?Employee $employee = null): float
    {
        if ($this->calculation_type === 'saudi_article_107') {
            if ($basicSalary > 0) {
                return static::calculateSaudiOvertimePay($hours, $basicSalary, $fixedAllowances);
            }
            // Fallback rate jika gaji pokok belum terset atau pengujian nominal tarif acuan
            if ((float) $this->rate_per_hour > 0) {
                $mult = (float) $this->multiplier > 0 ? (float) $this->multiplier : 1.5;
                return round($hours * (float) $this->rate_per_hour * $mult, 2);
            }
            return static::calculateSaudiOvertimePay($hours, $basicSalary, $fixedAllowances);
        }

        if ($this->calculation_type === 'depnaker_formula') {
            if ($basicSalary > 0) {
                $divisor = (int) SystemSetting::get('overtime_depnaker_divisor', 173);
                $hourlyRate = $divisor > 0 ? ($basicSalary / $divisor) : ($basicSalary / 173);
                return round($hours * $hourlyRate * (float) $this->multiplier, 2);
            }
            if ((float) $this->rate_per_hour > 0) {
                return round($hours * (float) $this->rate_per_hour * (float) $this->multiplier, 2);
            }
            return 0.0;
        }

        if ($this->calculation_type === 'percentage') {
            $hourlyRate = $basicSalary * ((float) $this->multiplier / 100);
            return round($hours * $hourlyRate, 2);
        }

        $multiplier = (float) $this->multiplier > 0 ? (float) $this->multiplier : 1.0;
        return round($hours * (float) $this->rate_per_hour * $multiplier, 2);
    }

    /**
     * Menghitung upah lembur resmi Arab Saudi berdasarkan Pasal 107 UU Ketenagakerjaan:
     * - Upah per Jam = (Gaji Pokok + Tunjangan Tetap) ÷ 30 ÷ Jam Normal Harian (umumnya 8 jam, atau 6 jam saat Ramadan)
     * - Premi 50% = 50% × Upah Pokok per Jam (tanpa tunjangan)
     * - Upah Lembur per Jam = Upah per Jam + Premi 50%
     * - Total Upah Lembur = Jam Lembur × Upah Lembur per Jam
     */
    public static function calculateSaudiOvertimePay(
        float $hours,
        float $basicSalary,
        float $fixedAllowances = 0,
        ?int $dailyHours = null,
        ?int $monthDays = null
    ): float {
        $dailyHours = $dailyHours ?? (int) SystemSetting::get('overtime_saudi_daily_hours', 8);
        $isRamadan = (bool) SystemSetting::get('overtime_saudi_is_ramadan', false);
        if ($isRamadan && $dailyHours === 8) {
            $dailyHours = 6;
        }
        $monthDays = $monthDays ?? (int) SystemSetting::get('overtime_saudi_month_days', 30);

        $totalDivisor = ($monthDays > 0 ? $monthDays : 30) * ($dailyHours > 0 ? $dailyHours : 8);

        // 1. Upah per Jam (Actual Hourly Wage: Gaji Pokok + Tunjangan Tetap)
        $hourlyWage = ($basicSalary + $fixedAllowances) / $totalDivisor;

        // 2. Upah Pokok per Jam (Basic Hourly Wage: hanya Gaji Pokok)
        $basicHourlyWage = $basicSalary / $totalDivisor;

        // 3. Premi 50% (dihitung hanya dari upah pokok per jam)
        $premium50 = 0.50 * $basicHourlyWage;

        // 4. Upah Lembur per Jam
        $overtimeRatePerHour = $hourlyWage + $premium50;

        return round($hours * $overtimeRatePerHour, 2);
    }

    /**
     * Rincian kalkulasi komprehensif Pasal 107 UU Ketenagakerjaan Arab Saudi untuk audit & slip gaji.
     */
    public static function getSaudiCalculationBreakdown(
        float $hours,
        float $basicSalary,
        float $fixedAllowances = 0,
        ?int $dailyHours = null,
        ?int $monthDays = null
    ): array {
        $dailyHours = $dailyHours ?? (int) SystemSetting::get('overtime_saudi_daily_hours', 8);
        $isRamadan = (bool) SystemSetting::get('overtime_saudi_is_ramadan', false);
        if ($isRamadan && $dailyHours === 8) {
            $dailyHours = 6;
        }
        $monthDays = $monthDays ?? (int) SystemSetting::get('overtime_saudi_month_days', 30);

        $totalDivisor = ($monthDays > 0 ? $monthDays : 30) * ($dailyHours > 0 ? $dailyHours : 8);

        $hourlyWage = ($basicSalary + $fixedAllowances) / $totalDivisor;
        $basicHourlyWage = $basicSalary / $totalDivisor;
        $premium50 = 0.50 * $basicHourlyWage;
        $overtimeRatePerHour = $hourlyWage + $premium50;
        $totalPay = round($hours * $overtimeRatePerHour, 2);

        return [
            'legal_basis' => 'Pasal 107 UU Ketenagakerjaan Arab Saudi (Saudi Labor Law Art. 107)',
            'hours' => $hours,
            'basic_salary' => $basicSalary,
            'fixed_allowances' => $fixedAllowances,
            'total_salary' => $basicSalary + $fixedAllowances,
            'month_days' => $monthDays,
            'daily_hours' => $dailyHours,
            'is_ramadan' => $isRamadan,
            'total_divisor' => $totalDivisor,
            'hourly_wage' => round($hourlyWage, 2),
            'basic_hourly_wage' => round($basicHourlyWage, 2),
            'premium_50_percent' => round($premium50, 2),
            'overtime_rate_per_hour' => round($overtimeRatePerHour, 2),
            'total_overtime_pay' => $totalPay,
            'formula_display' => "({$basicSalary} + {$fixedAllowances}) ÷ {$monthDays} ÷ {$dailyHours} + (50% × {$basicSalary} ÷ {$monthDays} ÷ {$dailyHours}) = " . round($overtimeRatePerHour, 2) . "/jam",
        ];
    }

    /**
     * Resolve the appropriate OvertimeRate for a given date and employee.
     */
    public static function resolveForDate(string|Carbon $date, ?Employee $employee = null): ?self
    {
        $isSaudiBranch = false;
        if ($employee && $employee->branch) {
            $isSaudiBranch = ($employee->branch->country_code === 'SA' || $employee->branch->timezone === 'Asia/Riyadh');
        }

        $globalCalcType = SystemSetting::get('overtime_calculation_type', 'flat');

        // If company policy is explicitly Saudi Article 107 OR employee is in Saudi branch under auto_country
        if ($globalCalcType === 'saudi_article_107' || ($globalCalcType === 'auto_country' && $isSaudiBranch) || $isSaudiBranch) {
            $saudiRate = static::active()->where('calculation_type', 'saudi_article_107')->first();
            if ($saudiRate) return $saudiRate;
        }

        // If company policy is explicitly Depnaker RI (1/173 Gaji Pokok)
        if ($globalCalcType === 'depnaker') {
            $depnakerRate = static::active()->where('calculation_type', 'depnaker_formula')->first();
            if ($depnakerRate) return $depnakerRate;
        }

        $d = Carbon::parse($date);
        $isHoliday = Holiday::where('date', $d->format('Y-m-d'))->exists();
        $workDays = (int) SystemSetting::get('work_days_per_week', 5);
        $isWeekend = ($workDays === 6) ? ($d->dayOfWeek === 0) : ($d->dayOfWeek === 0 || $d->dayOfWeek === 6);

        if ($isHoliday) {
            $rate = static::active()->where('day_type', 'holiday')->first();
            if ($rate) return $rate;
        }

        if ($isWeekend) {
            $rate = static::active()->where('day_type', 'weekend')->first();
            if ($rate) return $rate;
        }

        $weekdayRate = static::active()->where('day_type', 'weekday')->first();
        if ($weekdayRate) return $weekdayRate;

        // Fallback to default or any active
        return static::active()->where('is_default', true)->first()
            ?? static::active()->first();
    }
}
