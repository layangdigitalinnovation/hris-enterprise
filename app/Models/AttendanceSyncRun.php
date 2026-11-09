<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSyncRun extends Model
{
    use HasFactory;

    protected $table = 'attendance_sync_runs';

    protected $fillable = [
        'device_id',
        'run_at',
        'trigger_type',
        'records_fetched',
        'records_inserted',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'run_at' => 'datetime',
            'records_fetched' => 'integer',
            'records_inserted' => 'integer',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
