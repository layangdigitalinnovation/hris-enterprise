<?php

namespace App\Models;

use App\Helpers\CurrencyHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'investor_id',
        'distribution_number',
        'type',
        'period_label',
        'distribution_date',
        'currency',
        'basis_amount',
        'percentage_applied',
        'amount',
        'status',
        'payment_date',
        'payment_method',
        'payment_reference',
        'attachment_path',
        'notes',
        'created_by',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'distribution_date' => 'date',
            'payment_date' => 'date',
            'basis_amount' => 'decimal:2',
            'percentage_applied' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    public function investor(): BelongsTo
    {
        return $this->belongsTo(Investor::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getPeriodLabelAttribute(?string $value): ?string
    {
        if (empty($value)) {
            return $value;
        }

        if (app()->getLocale() === 'ar') {
            $months = [
                'Januari' => 'يناير', 'January' => 'يناير',
                'Februari' => 'فبراير', 'February' => 'فبراير',
                'Maret' => 'مارس', 'March' => 'مارس',
                'April' => 'أبريل',
                'Mei' => 'مايو', 'May' => 'مايو',
                'Juni' => 'يونيو', 'June' => 'يونيو',
                'Juli' => 'يوليو', 'July' => 'يوليو',
                'Agustus' => 'أغسطس', 'August' => 'أغسطس',
                'September' => 'سبتمبر',
                'Oktober' => 'أكتوبر', 'October' => 'أكتوبر',
                'November' => 'نوفمبر',
                'Desember' => 'ديسمبر', 'December' => 'ديسمبر',
            ];
            return strtr($value, $months);
        }

        return $value;
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'profit_share' => __('Bagi Hasil (Dividen)'),
            'stipend' => __('Gaji Khusus / Imbalan'),
            'capital_return' => __('Pengembalian Modal Pokok'),
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'bank_transfer' => __('Transfer Bank'),
            'cash' => __('Tunai / Kas'),
            'cheque' => __('Cek / Bilyet Giro'),
            default => $this->payment_reference ? __('Transfer') : '-',
        };
    }

    public static function generateNumber(): string
    {
        $prefix = 'DST-' . date('Ym') . '-';
        $last = self::where('distribution_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('distribution_number');

        $num = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $m)) {
            $num = (int) $m[1] + 1;
        }

        return $prefix . str_pad((string) $num, 3, '0', STR_PAD_LEFT);
    }
}

