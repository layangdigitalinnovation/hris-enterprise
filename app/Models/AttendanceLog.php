<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'employee_id',
        'device_pin',
        'log_time',
        'verify_type',
        'log_type',
        'raw_payload',
        'hash_signature',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'log_time' => 'datetime',
            'raw_payload' => 'array',
        ];
    }

    public static function generateHash(int|string|null $deviceId, string $pin, string $logTime): string
    {
        return hash('sha256', ($deviceId ?? '0') . '_' . trim($pin) . '_' . trim($logTime));
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the log's timezone (from payload, branch, or app config).
     */
    public function getTimezoneAttribute(): string
    {
        return $this->raw_payload['branch_timezone']
            ?? $this->raw_payload['timezone']
            ?? $this->device?->branch?->timezone
            ?? $this->employee?->branch?->timezone
            ?? config('app.timezone', 'Asia/Jakarta');
    }

    /**
     * Get log_time parsed in its actual local timezone.
     */
    public function getLogTimeCarbonAttribute(): Carbon
    {
        $tz = $this->timezone;
        $raw = $this->getRawOriginal('log_time');
        return Carbon::parse($raw, $tz);
    }

    /**
     * Accurate relative time (e.g. "10 menit yang lalu").
     */
    public function getRelativeTimeAttribute(): string
    {
        $tz = $this->timezone;
        $time = $this->log_time_carbon;
        $now = Carbon::now($tz);
        return $time->locale(app()->getLocale())->diffForHumans($now);
    }

    /**
     * Formatted log type (Check-In or Check-Out).
     */
    public function getTypeLabelAttribute(): string
    {
        $type = strtolower((string) $this->log_type);
        return match ($type) {
            'check_in' => __('Check-In'),
            'check_out' => __('Check-Out'),
            'break_out' => __('Istirahat Keluar'),
            'break_in' => __('Kembali Masuk'),
            'overtime_in' => __('Lembur Masuk'),
            'overtime_out' => __('Lembur Keluar'),
            default => strtoupper(str_replace('_', ' ', (string) $this->log_type)),
        };
    }

    /**
     * Source / Origin name (Visit Client, GPS Branch, or Solution Device).
     */
    public function getSourceNameAttribute(): string
    {
        if ($this->verify_type === 'visit') {
            $client = $this->raw_payload['client_name'] ?? __('Kunjungan Lapangan');
            return __('Visit: :client', ['client' => $client]);
        }

        if ($this->verify_type === 'gps') {
            $branch = $this->raw_payload['branch_name'] ?? $this->employee?->branch?->name ?? __('Kantor Cabang');
            return __('GPS (:branch)', ['branch' => $branch]);
        }

        if ($this->device) {
            return $this->device->name;
        }

        return __('Mesin Solution');
    }

    /**
     * Source icon representation.
     */
    public function getSourceIconAttribute(): string
    {
        return match ($this->verify_type) {
            'visit' => '🚗',
            'gps' => '📍',
            'fingerprint' => '👆',
            'face' => '👤',
            'rfid' => '💳',
            default => '📟',
        };
    }

    /**
     * Verification method badge label.
     */
    public function getVerifyTypeLabelAttribute(): string
    {
        return match ($this->verify_type) {
            'visit' => __('Absen Visit'),
            'gps' => __('GPS Geofence'),
            'fingerprint' => 'Fingerprint',
            'face' => 'Face ID',
            'rfid' => 'RFID Card',
            default => ucfirst($this->verify_type ?? 'Manual'),
        };
    }

    /**
     * Full readable activity line, e.g. "Check-In via Visit: PT Citra Persada".
     */
    public function getActivityTextAttribute(): string
    {
        $type = $this->type_label;
        $source = $this->source_name;
        return __(':type via :source', ['type' => $type, 'source' => $source]);
    }
}
