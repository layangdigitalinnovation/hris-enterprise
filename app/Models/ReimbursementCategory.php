<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReimbursementCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'currency',
        'max_amount',
        'max_amount_sar',
        'requires_receipt',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'max_amount' => 'decimal:2',
            'max_amount_sar' => 'decimal:2',
            'requires_receipt' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ReimbursementRequest::class, 'category_id');
    }

    /**
     * Cek apakah kategori mendukung mata uang tertentu
     */
    public function supportsCurrency(string $curr): bool
    {
        if (empty($this->currency) || $this->currency === 'ALL') {
            return true;
        }

        return strtoupper($this->currency) === strtoupper($curr);
    }

    /**
     * Ambil batas nominal plafon sesuai mata uang yang diajukan
     */
    public function getMaxAmountForCurrency(string $curr): ?float
    {
        $curr = strtoupper($curr);

        if ($curr === 'SAR') {
            return $this->max_amount_sar ? (float) $this->max_amount_sar : ($this->currency === 'SAR' ? (float) $this->max_amount : null);
        }

        return $this->max_amount ? (float) $this->max_amount : null;
    }

    /**
     * Label badge mata uang
     */
    public function getCurrencyBadgeLabelAttribute(): string
    {
        return match (strtoupper($this->currency ?? 'ALL')) {
            'IDR' => '🇮🇩 IDR (Rupiah)',
            'SAR' => '🇸🇦 SAR (Riyal)',
            default => '🌐 Semua Mata Uang (IDR & SAR)',
        };
    }
}

