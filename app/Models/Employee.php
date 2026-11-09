<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'employee_code',
        'nik',
        'first_name',
        'last_name',
        'email',
        'phone',
        'gender',
        'birth_place',
        'birth_date',
        'marital_status',
        'religion',
        'address',
        'branch_id',
        'payroll_template_id',
        'currency',
        'department_id',
        'position_id',
        'manager_id',
        'join_date',
        'end_date',
        'employment_status',
        'status',
        'bank_name',
        'bank_account_no',
        'bank_account_holder',
        'npwp',
        'bpjs_ketenagakerjaan',
        'bpjs_kesehatan',
        'photo_path',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'join_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Get effective salary currency for this employee (IDR or SAR).
     */
    public function getEffectiveCurrencyAttribute(): string
    {
        if (!empty($this->currency)) {
            return strtoupper($this->currency);
        }
        if ($this->branch) {
            return $this->branch->effective_currency;
        }
        return \App\Helpers\CurrencyHelper::defaultCurrency();
    }

    /**
     * Determine if employee is stationed in Saudi Arabia or paid in SAR.
     */
    public function isSaudi(): bool
    {
        return ($this->effective_currency === 'SAR' || ($this->branch && $this->branch->isSaudi()));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function deviceMappings(): HasMany
    {
        return $this->hasMany(DeviceEmployeeMapping::class);
    }

    public function shiftAssignments(): HasMany
    {
        return $this->hasMany(EmployeeShiftAssignment::class);
    }

    public function attendanceSummaries(): HasMany
    {
        return $this->hasMany(AttendanceDailySummary::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function overtimeRequests(): HasMany
    {
        return $this->hasMany(OvertimeRequest::class);
    }

    public function compensations(): HasMany
    {
        return $this->hasMany(EmployeeCompensation::class);
    }

    public function reimbursementRequests(): HasMany
    {
        return $this->hasMany(ReimbursementRequest::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(EmployeeLoan::class);
    }

    public function activeLoans(): HasMany
    {
        return $this->hasMany(EmployeeLoan::class)->whereIn('status', ['active', 'disbursed']);
    }

    public function payrollTemplate(): BelongsTo
    {
        return $this->belongsTo(PayrollTemplate::class, 'payroll_template_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(AttendanceVisit::class);
    }

    public function resignations(): HasMany
    {
        return $this->hasMany(EmployeeResignation::class)->orderByDesc('id');
    }

    public function activeResignation(): HasOne
    {
        return $this->hasOne(EmployeeResignation::class)->whereIn('status', ['pending', 'approved'])->latestOfMany();
    }

    public function careerTransitions(): HasMany
    {
        return $this->hasMany(EmployeeCareerTransition::class)->orderByDesc('id');
    }

    public function getTenureFormattedAttribute(): string
    {
        if (!$this->join_date) {
            return '-';
        }
        $endDate = $this->end_date ?? now();
        $diff = $this->join_date->diff($endDate);

        $parts = [];
        if ($diff->y > 0) {
            $parts[] = $diff->y . ' ' . __('Tahun');
        }
        if ($diff->m > 0) {
            $parts[] = $diff->m . ' ' . __('Bulan');
        }
        if (empty($parts) || $diff->y === 0 && $diff->m === 0) {
            $parts[] = max(1, $diff->d) . ' ' . __('Hari');
        }

        return implode(' ', $parts);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'active' => __('Aktif'),
            'inactive' => __('Non-Aktif'),
            'resigned' => __('Resign'),
            'terminated' => __('Diberhentikan'),
            default => __($this->status ? ucfirst($this->status) : '-')
        };
    }

    public function getStatusBadgeClassesAttribute(): string
    {
        return match($this->status) {
            'active' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'inactive' => 'bg-slate-100 text-slate-700 border border-slate-200',
            'resigned' => 'bg-rose-50 text-rose-700 border border-rose-200',
            'terminated' => 'bg-red-50 text-red-700 border border-red-200',
            default => 'bg-slate-100 text-slate-600 border border-slate-200'
        };
    }
}
