<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class EmployeeResignation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employee_resignations';

    protected $fillable = [
        'resignation_number',
        'employee_id',
        'branch_id',
        'submission_date',
        'requested_last_day',
        'approved_last_day',
        'reason_type',
        'reason_detail',
        'handover_notes',
        'resignation_letter_path',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejection_reason',
        'exit_interview_notes',
        'clearance_assets_returned',
        'clearance_assets_notes',
        'clearance_finance_settled',
        'clearance_finance_notes',
        'clearance_handover_completed',
        'clearance_handover_notes',
        'completed_by',
        'completed_at',
        'completion_notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'submission_date' => 'date',
            'requested_last_day' => 'date',
            'approved_last_day' => 'date',
            'approved_at' => 'datetime',
            'completed_at' => 'datetime',
            'clearance_assets_returned' => 'boolean',
            'clearance_finance_settled' => 'boolean',
            'clearance_handover_completed' => 'boolean',
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getEffectiveLastDayAttribute(): ?Carbon
    {
        return $this->approved_last_day ?? $this->requested_last_day;
    }

    public function getNoticeDaysCountAttribute(): int
    {
        if (!$this->submission_date || !$this->requested_last_day) {
            return 0;
        }
        return (int) $this->submission_date->diffInDays($this->requested_last_day, false);
    }

    public function getIsStandardNoticeAttribute(): bool
    {
        return $this->notice_days_count >= 30;
    }

    public function getClearancePercentageAttribute(): int
    {
        $done = 0;
        if ($this->clearance_assets_returned) $done++;
        if ($this->clearance_finance_settled) $done++;
        if ($this->clearance_handover_completed) $done++;

        return (int) round(($done / 3) * 100);
    }

    public function isFullyCleared(): bool
    {
        return $this->clearance_assets_returned && $this->clearance_finance_settled && $this->clearance_handover_completed;
    }

    public function getResignationLetterUrlAttribute(): ?string
    {
        return $this->resignation_letter_path ? Storage::url($this->resignation_letter_path) : null;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => __('Menunggu Approval'),
            'approved' => __('Disetujui (Masa Notice)'),
            'completed' => __('Selesai Offboarding'),
            'rejected' => __('Ditolak'),
            'cancelled' => __('Dibatalkan'),
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassesAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-amber-50 text-amber-700 border-amber-200/80',
            'approved' => 'bg-blue-50 text-blue-700 border-blue-200/80',
            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
            'rejected' => 'bg-rose-50 text-rose-700 border-rose-200/80',
            'cancelled' => 'bg-slate-100 text-slate-600 border-slate-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }

    public function getReasonLabelAttribute(): string
    {
        return match ($this->reason_type) {
            'career_growth' => __('Peluang Karir Lebih Baik'),
            'relocation' => __('Pindah Domisili / Relokasi'),
            'health' => __('Kesehatan / Alasan Medis'),
            'personal' => __('Alasan Pribadi'),
            'family' => __('Alasan Keluarga / Rumah Tangga'),
            'education' => __('Melanjutkan Pendidikan'),
            'compensation' => __('Kompensasi & Benefit'),
            'entrepreneurship' => __('Wirausaha / Usaha Mandiri'),
            'other' => __('Lain-lain'),
            default => ucfirst(str_replace('_', ' ', $this->reason_type ?? 'other')),
        };
    }

    public static function generateResignationNumber(): string
    {
        $prefix = 'RSG-' . date('Ym') . '-';
        $lastRecord = self::withTrashed()
            ->where('resignation_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $seq = 1;
        if ($lastRecord) {
            $lastSeq = (int) substr($lastRecord->resignation_number, -4);
            $seq = $lastSeq + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}

