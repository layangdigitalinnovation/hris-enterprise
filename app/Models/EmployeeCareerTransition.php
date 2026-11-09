<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeCareerTransition extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employee_career_transitions';

    protected $fillable = [
        'transition_number',
        'letter_number',
        'employee_id',
        'type',
        'from_branch_id',
        'from_department_id',
        'from_position_id',
        'from_manager_id',
        'from_payroll_template_id',
        'from_salary',
        'from_currency',
        'to_branch_id',
        'to_department_id',
        'to_position_id',
        'to_manager_id',
        'to_payroll_template_id',
        'to_salary',
        'to_currency',
        'request_date',
        'effective_date',
        'reason',
        'notes',
        'attachment_path',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejection_reason',
        'applied_by',
        'applied_at',
        'execution_notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'request_date' => 'date',
            'effective_date' => 'date',
            'from_salary' => 'decimal:2',
            'to_salary' => 'decimal:2',
            'approved_at' => 'datetime',
            'applied_at' => 'datetime',
        ];
    }

    public static function generateTransitionNumber(): string
    {
        $prefix = 'TRN-' . date('Ym') . '-';
        $latest = static::withTrashed()
            ->where('transition_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $seq = 1;
        if ($latest && preg_match('/-(\d{4})$/', $latest->transition_number, $matches)) {
            $seq = (int)$matches[1] + 1;
        }

        return $prefix . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    public function fromPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'from_position_id');
    }

    public function toPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'to_position_id');
    }

    public function fromManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'from_manager_id');
    }

    public function toManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'to_manager_id');
    }

    public function fromPayrollTemplate(): BelongsTo
    {
        return $this->belongsTo(PayrollTemplate::class, 'from_payroll_template_id');
    }

    public function toPayrollTemplate(): BelongsTo
    {
        return $this->belongsTo(PayrollTemplate::class, 'to_payroll_template_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function applier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accessors
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'promotion' => __('Promosi Jabatan'),
            'transfer' => __('Mutasi Lokasi / Cabang'),
            'rotation' => __('Rotasi Pekerjaan'),
            'demotion' => __('Demosi'),
            default => ucfirst($this->type)
        };
    }

    public function getTypeBadgeClassesAttribute(): string
    {
        return match($this->type) {
            'promotion' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'transfer' => 'bg-blue-50 text-blue-700 border-blue-200',
            'rotation' => 'bg-purple-50 text-purple-700 border-purple-200',
            'demotion' => 'bg-amber-50 text-amber-700 border-amber-200',
            default => 'bg-slate-50 text-slate-700 border-slate-200'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => __('Menunggu Review'),
            'approved' => __('Disetujui (Siap Berlaku)'),
            'applied' => __('Telah Diterapkan'),
            'rejected' => __('Ditolak'),
            'cancelled' => __('Dibatalkan'),
            default => ucfirst($this->status)
        };
    }

    public function getStatusBadgeClassesAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
            'approved' => 'bg-blue-50 text-blue-700 border-blue-200',
            'applied' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
            'cancelled' => 'bg-slate-100 text-slate-600 border-slate-200',
            default => 'bg-slate-50 text-slate-700 border-slate-200'
        };
    }

    public function getIsEffectiveAttribute(): bool
    {
        return $this->effective_date && $this->effective_date->isPast() || $this->effective_date->isToday();
    }

    public function getSalaryDiffAttribute(): ?float
    {
        if ($this->from_salary !== null && $this->to_salary !== null) {
            return (float)$this->to_salary - (float)$this->from_salary;
        }
        return null;
    }

    /**
     * Apply transition changes directly to the master employee record.
     */
    public function applyToEmployee(User $user, ?string $executionNotes = null): bool
    {
        $employee = $this->employee;
        if (!$employee) {
            return false;
        }

        $updates = [];
        if ($this->to_branch_id) {
            $updates['branch_id'] = $this->to_branch_id;
        }
        if ($this->to_department_id) {
            $updates['department_id'] = $this->to_department_id;
        }
        if ($this->to_position_id) {
            $updates['position_id'] = $this->to_position_id;
        }
        if ($this->to_manager_id !== null) {
            $updates['manager_id'] = $this->to_manager_id;
        }
        if ($this->to_payroll_template_id) {
            $updates['payroll_template_id'] = $this->to_payroll_template_id;
        }
        if ($this->to_currency) {
            $updates['currency'] = $this->to_currency;
        }

        if (!empty($updates)) {
            $employee->update($updates);
        }

        // If new salary specified, update or create basic compensation component
        if ($this->to_salary !== null && $this->to_salary > 0) {
            $basicComp = SalaryComponent::where('type', 'earning')
                ->where(function($q) {
                    $q->where('code', 'like', '%BASIC%')
                      ->orWhere('name', 'like', '%Pokok%')
                      ->orWhere('name', 'like', '%أساسي%');
                })->first();

            if ($basicComp) {
                EmployeeCompensation::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'salary_component_id' => $basicComp->id,
                    ],
                    [
                        'amount' => $this->to_salary,
                    ]
                );
            }
        }

        $this->update([
            'status' => 'applied',
            'applied_by' => $user->id,
            'applied_at' => now(),
            'execution_notes' => $executionNotes,
        ]);

        return true;
    }
}
