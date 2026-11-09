<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class PayrollTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'currency',
        'branch_id',
        'department_id',
        'base_salary',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollTemplateItem::class)->orderBy('order_index')->orderBy('id');
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(PayrollTemplateItem::class)
            ->where('type', 'earning')
            ->orderBy('order_index')
            ->orderBy('id');
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(PayrollTemplateItem::class)
            ->where('type', 'deduction')
            ->orderBy('order_index')
            ->orderBy('id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Estimated Take-Home Pay simulation based on standard monthly workdays (22 days).
     */
    public function getEstimatedSimulationAttribute(): array
    {
        $base = (float) $this->base_salary;
        $totalAllowances = 0.0;
        $totalDeductions = 0.0;
        $workdays = 22; // default standard days

        foreach ($this->items as $item) {
            $amt = 0.0;
            $mode = $item->calculation_mode;
            $rate = (float) $item->rate_value;
            $fixed = (float) $item->amount;

            if ($mode === 'fixed') {
                $amt = $fixed > 0 ? $fixed : ($rate > 0 ? $rate : 0.0);
            } elseif ($mode === 'percentage') {
                $amt = round($base * ($rate / 100), 2);
            } elseif ($mode === 'per_attendance') {
                $amt = round($workdays * $rate, 2);
            } elseif ($mode === 'per_late_minute') {
                $amt = 0.0; // variable upon attendance
            } else {
                $amt = $fixed;
            }

            if ($item->type === 'earning') {
                if ($item->component?->code === 'BASIC') {
                    // Skip basic if represented by component to avoid duplicate
                    continue;
                }
                $totalAllowances += $amt;
            } else {
                $totalDeductions += $amt;
            }
        }

        $gross = $base + $totalAllowances;
        $net = max(0, $gross - $totalDeductions);

        return [
            'base_salary' => $base,
            'total_allowances' => $totalAllowances,
            'total_deductions' => $totalDeductions,
            'gross_salary' => $gross,
            'net_salary' => $net,
            'currency' => $this->currency,
        ];
    }

    /**
     * Apply this template to an individual employee.
     */
    public function applyToEmployee(Employee $employee, bool $overwriteExisting = true): void
    {
        DB::transaction(function () use ($employee, $overwriteExisting) {
            // 1. Link employee to this template
            $employee->payroll_template_id = $this->id;
            $employee->currency = $this->currency;
            $employee->save();

            // 2. Sync Basic Salary Component
            $basicComp = SalaryComponent::where('code', 'BASIC')->first();
            if ($basicComp && (float) $this->base_salary > 0) {
                EmployeeCompensation::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'salary_component_id' => $basicComp->id,
                    ],
                    [
                        'amount' => $this->base_salary,
                    ]
                );
            }

            // 3. If overwriteExisting is true, clear old compensations not part of this template
            if ($overwriteExisting) {
                $templateComponentIds = $this->items->pluck('salary_component_id')->all();
                if ($basicComp) {
                    $templateComponentIds[] = $basicComp->id;
                }
                EmployeeCompensation::where('employee_id', $employee->id)
                    ->whereNotIn('salary_component_id', $templateComponentIds)
                    ->delete();
            }

            // 4. Sync each template item
            foreach ($this->items as $item) {
                if ($item->component?->code === 'BASIC') {
                    continue;
                }

                $existing = EmployeeCompensation::where('employee_id', $employee->id)
                    ->where('salary_component_id', $item->salary_component_id)
                    ->first();

                if ($existing && !$overwriteExisting) {
                    continue;
                }

                $amt = (float) $item->amount;
                // If amount is zero and rate_value > 0 for fixed mode, use rate_value
                if ($amt <= 0 && $item->calculation_mode === 'fixed' && (float) $item->rate_value > 0) {
                    $amt = (float) $item->rate_value;
                } elseif ($amt <= 0 && $item->calculation_mode === 'percentage' && (float) $item->rate_value > 0 && (float) $this->base_salary > 0) {
                    $amt = round((float) $this->base_salary * ((float) $item->rate_value / 100), 2);
                }

                if ($amt > 0) {
                    EmployeeCompensation::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'salary_component_id' => $item->salary_component_id,
                        ],
                        [
                            'amount' => $amt,
                        ]
                    );
                } elseif ($overwriteExisting) {
                    EmployeeCompensation::where('employee_id', $employee->id)
                        ->where('salary_component_id', $item->salary_component_id)
                        ->delete();
                }
            }
        });
    }

    /**
     * Bulk apply this template to an array of employee IDs.
     */
    public function applyToEmployees(array $employeeIds, bool $overwriteExisting = true): int
    {
        $count = 0;
        $employees = Employee::whereIn('id', $employeeIds)->get();

        foreach ($employees as $emp) {
            $this->applyToEmployee($emp, $overwriteExisting);
            $count++;
        }

        return $count;
    }
}
