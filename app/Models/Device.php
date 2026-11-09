<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'device_model',
        'ip_address',
        'port',
        'comm_key',
        'serial_number',
        'branch_id',
        'location',
        'status',
        'sync_method',
        'auto_sync',
        'sync_interval_minutes',
        'last_sync_at',
        'last_error_message',
    ];

    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'auto_sync' => 'boolean',
            'sync_interval_minutes' => 'integer',
            'last_sync_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function employeeMappings(): HasMany
    {
        return $this->hasMany(DeviceEmployeeMapping::class);
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function syncRuns(): HasMany
    {
        return $this->hasMany(AttendanceSyncRun::class);
    }
}
