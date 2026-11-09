<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'default_quota',
        'is_paid',
        'requires_document',
        'max_consecutive_days',
        'gender_restriction',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_quota' => 'integer',
            'is_paid' => 'boolean',
            'requires_document' => 'boolean',
            'max_consecutive_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function balances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
