<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'role_id',
        'phone',
        'avatar_url',
        'is_active',
        'all_branches',
        'all_departments',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'all_branches' => 'boolean',
            'all_departments' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function roleModel(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_has_roles', 'user_id', 'role_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_has_permissions', 'user_id', 'permission_id');
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'user_has_branches', 'user_id', 'branch_id');
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'user_has_departments', 'user_id', 'department_id');
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function investor(): HasOne
    {
        return $this->hasOne(Investor::class);
    }

    public function isInvestor(): bool
    {
        return $this->role === 'investor';
    }

    public function canManageInvestors(): bool
    {
        return in_array($this->role, ['super_admin', 'hr_admin', 'finance']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isHrAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'hr_admin']);
    }

    public function isManager(): bool
    {
        return in_array($this->role, ['super_admin', 'hr_admin', 'manager']);
    }

    public function canApprove(): bool
    {
        return in_array($this->role, ['super_admin', 'hr_admin', 'manager']);
    }

    public function canManageReimbursements(): bool
    {
        return in_array($this->role, ['super_admin', 'hr_admin', 'finance', 'manager']);
    }

    public function canApproveReimbursement(): bool
    {
        return in_array($this->role, ['super_admin', 'hr_admin', 'manager']);
    }

    public function canPayReimbursement(): bool
    {
        return $this->isFinance() || $this->isSuperAdmin();
    }

    public function canManageLoans(): bool
    {
        return in_array($this->role, ['super_admin', 'hr_admin', 'finance', 'manager']);
    }

    public function canApproveLoan(): bool
    {
        return in_array($this->role, ['super_admin', 'hr_admin', 'manager']);
    }

    public function canManageResignations(): bool
    {
        return in_array($this->role, ['super_admin', 'hr_admin', 'manager']);
    }

    public function canApproveResignation(): bool
    {
        return in_array($this->role, ['super_admin', 'hr_admin', 'manager']);
    }

    public function canDisburseLoan(): bool
    {
        return $this->isFinance() || $this->isSuperAdmin();
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function isFinance(): bool
    {
        return in_array($this->role, ['super_admin', 'finance']) || 
               ($this->employee?->department?->code === 'FIN');
    }

    public function canManagePayroll(): bool
    {
        return in_array($this->role, ['super_admin', 'hr_admin', 'finance']) ||
               ($this->employee?->department?->code === 'FIN');
    }

    public function canManageEmployees(): bool
    {
        return in_array($this->role, ['super_admin', 'hr_admin']);
    }

    public function canManageDevices(): bool
    {
        return in_array($this->role, ['super_admin', 'hr_admin']);
    }

    public function canManageSettings(): bool
    {
        return in_array($this->role, ['super_admin', 'hr_admin']);
    }

    public function canViewCompensation(): bool
    {
        return in_array($this->role, ['super_admin', 'hr_admin', 'finance']) ||
               ($this->employee?->department?->code === 'FIN');
    }

    public function canManageCareerTransitions(): bool
    {
        return in_array($this->role, ['super_admin', 'hr_admin']);
    }

    public function canApproveCareerTransition(): bool
    {
        return in_array($this->role, ['super_admin', 'hr_admin', 'manager']);
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            if (in_array($this->role, $roles)) return true;
            if ($this->roleModel && in_array($this->roleModel->key, $roles)) return true;
            return $this->roles()->whereIn('key', $roles)->exists();
        }
        if ($this->role === $roles) return true;
        if ($this->roleModel && $this->roleModel->key === $roles) return true;
        return $this->roles()->where('key', $roles)->exists();
    }

    public function hasPermissionTo(string|Permission $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $name = is_string($permission) ? $permission : $permission->name;

        // Check direct user permissions
        if ($this->permissions()->where('name', $name)->exists()) {
            return true;
        }

        // Check assigned primary role permissions
        if ($this->roleModel && $this->roleModel->hasPermissionTo($name)) {
            return true;
        }

        // Check any secondary roles permissions
        return $this->roles()->whereHas('permissions', function ($q) use ($name) {
            $q->where('name', $name);
        })->exists();
    }

    public function canAccessBranch($branchId): bool
    {
        if ($this->isSuperAdmin() || $this->all_branches) {
            return true;
        }
        return $this->branches()->where('branches.id', $branchId)->exists();
    }

    public function canAccessDepartment($departmentId): bool
    {
        if ($this->isSuperAdmin() || $this->all_departments) {
            return true;
        }
        return $this->departments()->where('departments.id', $departmentId)->exists();
    }

    public function getRoleDisplayNameAttribute(): string
    {
        if ($this->roleModel) {
            return __($this->roleModel->name);
        }
        return match($this->role) {
            'super_admin' => __('Super Admin'),
            'hr_admin' => __('HRD Admin'),
            'manager' => __('Manager'),
            'finance' => __('Finance'),
            'investor' => __('Investor'),
            'employee' => __('Karyawan'),
            default => __(ucfirst(str_replace('_', ' ', $this->role ?? 'User'))),
        };
    }
}
