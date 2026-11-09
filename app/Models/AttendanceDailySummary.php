<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceDailySummary extends Model
{
    use HasFactory;

    protected $table = 'attendance_daily_summaries';

    protected $fillable = [
        'employee_id',
        'date',
        'shift_id',
        'clock_in',
        'clock_out',
        'late_minutes',
        'early_leave_minutes',
        'work_hours',
        'overtime_hours',
        'status',
        'is_locked',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'late_minutes' => 'integer',
            'early_leave_minutes' => 'integer',
            'work_hours' => 'decimal:2',
            'overtime_hours' => 'decimal:2',
            'is_locked' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'present' => ['label' => 'Hadir', 'color' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            'late' => ['label' => 'Terlambat', 'color' => 'bg-amber-50 text-amber-700 border-amber-200'],
            'early_leave' => ['label' => 'Pulang Awal', 'color' => 'bg-orange-50 text-orange-700 border-orange-200'],
            'absent' => ['label' => 'Alpa', 'color' => 'bg-rose-50 text-rose-700 border-rose-200'],
            'on_leave' => ['label' => 'Cuti', 'color' => 'bg-blue-50 text-blue-700 border-blue-200'],
            'sick' => ['label' => 'Sakit', 'color' => 'bg-purple-50 text-purple-700 border-purple-200'],
            'permission' => ['label' => 'Izin', 'color' => 'bg-cyan-50 text-cyan-700 border-cyan-200'],
            'holiday' => ['label' => 'Libur Nasional', 'color' => 'bg-slate-100 text-slate-700 border-slate-200'],
            'day_off' => ['label' => 'Hari Libur Shift', 'color' => 'bg-slate-100 text-slate-700 border-slate-200'],
            default => ['label' => ucfirst($this->status), 'color' => 'bg-slate-100 text-slate-700 border-slate-200'],
        };
    }
}
