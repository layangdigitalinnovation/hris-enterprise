<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'calculation_mode',
        'rate_value',
        'is_taxable',
        'is_fixed',
        'default_amount',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate_value' => 'decimal:2',
            'is_taxable' => 'boolean',
            'is_fixed' => 'boolean',
            'default_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function compensations(): HasMany
    {
        return $this->hasMany(EmployeeCompensation::class);
    }
}
