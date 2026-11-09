<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payslip extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_item_id',
        'slip_number',
        'generated_at',
        'pdf_path',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    public function payrollItem(): BelongsTo
    {
        return $this->belongsTo(PayrollItem::class);
    }
}
