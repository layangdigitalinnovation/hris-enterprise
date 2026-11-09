<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ReimbursementRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'claim_number',
        'employee_id',
        'branch_id',
        'category_id',
        'title',
        'description',
        'claim_date',
        'currency',
        'amount',
        'approved_amount',
        'receipt_path',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'paid_by',
        'paid_at',
        'payment_method',
        'payment_reference',
        'payment_receipt_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'claim_date' => 'date',
            'amount' => 'decimal:2',
            'approved_amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ReimbursementCategory::class, 'category_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public static function generateClaimNumber(): string
    {
        $yearMonth = date('Ym');
        $prefix = "RMB-{$yearMonth}-";
        
        $latest = static::where('claim_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $num = (int) substr($latest->claim_number, -4);
            $next = str_pad($num + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $next = '0001';
        }

        return $prefix . $next;
    }

    public function getReceiptUrlAttribute(): ?string
    {
        return $this->receipt_path ? Storage::url($this->receipt_path) : null;
    }

    public function getPaymentReceiptUrlAttribute(): ?string
    {
        return $this->payment_receipt_path ? Storage::url($this->payment_receipt_path) : null;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function canBeEdited(): bool
    {
        return $this->status === 'pending';
    }

    public function canBeDeleted(): bool
    {
        return $this->status === 'pending';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => __('Menunggu Approval'),
            'approved' => __('Disetujui (Siap Bayar)'),
            'rejected' => __('Ditolak'),
            'paid' => __('Selesai Dibayar (Lunas)'),
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassesAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-amber-50 text-amber-700 border-amber-200/60',
            'approved' => 'bg-blue-50 text-blue-700 border-blue-200/60',
            'rejected' => 'bg-rose-50 text-rose-700 border-rose-200/60',
            'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60',
            default => 'bg-slate-50 text-slate-700 border-slate-200/60',
        };
    }
}

