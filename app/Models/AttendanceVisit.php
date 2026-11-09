<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'branch_id',
        'visit_number',
        'client_name',
        'purpose',
        'check_in_at',
        'check_in_lat',
        'check_in_lon',
        'check_in_address',
        'check_out_at',
        'check_out_lat',
        'check_out_lon',
        'check_out_address',
        'outcome',
        'duration_minutes',
        'status',
        'photo_path',
        'timezone',
    ];

    protected function casts(): array
    {
        return [
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
            'check_in_lat' => 'float',
            'check_in_lon' => 'float',
            'check_out_lat' => 'float',
            'check_out_lon' => 'float',
            'duration_minutes' => 'integer',
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

    public function isOngoing(): bool
    {
        return $this->status === 'ongoing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Format durasi kunjungan ke dalam jam dan menit
     */
    public function getFormattedDurationAttribute(): string
    {
        if ($this->isOngoing()) {
            $now = Carbon::now($this->timezone ?? 'Asia/Jakarta');
            $mins = max(1, $this->check_in_at->diffInMinutes($now));
        } else {
            $mins = $this->duration_minutes ?? 0;
        }

        $hours = intdiv($mins, 60);
        $remainder = $mins % 60;

        if ($hours > 0 && $remainder > 0) {
            return __(':h jam :m menit', ['h' => $hours, 'm' => $remainder]);
        } elseif ($hours > 0) {
            return __(':h jam', ['h' => $hours]);
        }

        return __(':m menit', ['m' => $remainder]);
    }

    /**
     * URL Google Maps koordinat check-in
     */
    public function getCheckInMapUrlAttribute(): string
    {
        return "https://www.google.com/maps?q={$this->check_in_lat},{$this->check_in_lon}";
    }

    /**
     * URL Google Maps koordinat check-out
     */
    public function getCheckOutMapUrlAttribute(): ?string
    {
        if ($this->check_out_lat === null || $this->check_out_lon === null) {
            return null;
        }
        return "https://www.google.com/maps?q={$this->check_out_lat},{$this->check_out_lon}";
    }

    /**
     * Badge status kunjungan
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'ongoing' => [
                'label' => __('Sedang Berlangsung'),
                'class' => 'bg-amber-50 text-amber-700 border-amber-200 animate-pulse',
                'dot' => 'bg-amber-500',
            ],
            'completed' => [
                'label' => __('Selesai (Checked Out)'),
                'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'dot' => 'bg-emerald-500',
            ],
            'cancelled' => [
                'label' => __('Dibatalkan'),
                'class' => 'bg-slate-100 text-slate-600 border-slate-200',
                'dot' => 'bg-slate-400',
            ],
            default => [
                'label' => ucfirst($this->status),
                'class' => 'bg-slate-100 text-slate-700 border-slate-200',
                'dot' => 'bg-slate-400',
            ],
        };
    }
}

