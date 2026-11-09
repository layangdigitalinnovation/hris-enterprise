<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'phone',
        'address',
        'latitude',
        'longitude',
        'timezone',
        'currency',
        'country_code',
        'radius_meters',
        'is_active',
    ];

    /**
     * Get effective operating currency for this branch (IDR or SAR).
     */
    public function getEffectiveCurrencyAttribute(): string
    {
        if (!empty($this->currency)) {
            return strtoupper($this->currency);
        }
        if ($this->country_code === 'SA' || $this->timezone === 'Asia/Riyadh') {
            return 'SAR';
        }
        return \App\Helpers\CurrencyHelper::defaultCurrency();
    }

    /**
     * Determine if branch is in Saudi Arabia.
     */
    public function isSaudi(): bool
    {
        return ($this->country_code === 'SA' || $this->timezone === 'Asia/Riyadh' || $this->effective_currency === 'SAR');
    }

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'radius_meters' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    /**
     * Hitung jarak (dalam meter) antara koordinat GPS target dengan kantor cabang menggunakan rumus Haversine.
     */
    public function calculateDistance(float $lat, float $lon): ?float
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        $earthRadius = 6371000; // Radius bumi dalam meter
        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($lat);
        $lonTo = deg2rad($lon);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * $earthRadius, 1);
    }

    /**
     * Cek apakah koordinat GPS berada di dalam batas radius presensi cabang.
     */
    public function isWithinRadius(float $lat, float $lon): array
    {
        $distance = $this->calculateDistance($lat, $lon);

        if ($distance === null) {
            return [
                'allowed' => false,
                'distance' => null,
                'radius' => (int) ($this->radius_meters ?? 100),
                'message' => __('Titik koordinat kantor cabang belum diatur.'),
            ];
        }

        $radius = (int) ($this->radius_meters ?? 100);
        $allowed = $distance <= $radius;

        return [
            'allowed' => $allowed,
            'distance' => $distance,
            'radius' => $radius,
            'message' => $allowed
                ? __('Lokasi berada di dalam radius presensi (:dist m dari batas :rad m).', ['dist' => $distance, 'rad' => $radius])
                : __('Lokasi berada di luar radius presensi (:dist m, melebihi batas :rad m).', ['dist' => $distance, 'rad' => $radius]),
        ];
    }

    /**
     * Dapatkan waktu lokal saat ini di zona waktu kantor cabang.
     */
    public function getLocalTime(): Carbon
    {
        $tz = $this->timezone ?: 'Asia/Jakarta';
        return Carbon::now($tz);
    }

    /**
     * Label zona waktu yang ramah pengguna.
     */
    public function getTimezoneLabelAttribute(): string
    {
        return match ($this->timezone) {
            'Asia/Jakarta' => 'WIB (UTC+7 - Jakarta/Jawa/Sumatera)',
            'Asia/Makassar' => 'WITA (UTC+8 - Makassar/Bali/Nusa Tenggara)',
            'Asia/Jayapura' => 'WIT (UTC+9 - Jayapura/Maluku/Papua)',
            'Asia/Riyadh' => 'AST (UTC+3 - Riyadh/Jeddah/Arab Saudi)',
            default => $this->timezone ?? 'Asia/Jakarta',
        };
    }

    /**
     * Kode negara (ID atau SA).
     */
    public function getCountryCodeAttribute(): string
    {
        return $this->timezone === 'Asia/Riyadh' ? 'SA' : 'ID';
    }

    /**
     * Bendera negara (🇮🇩 atau 🇸🇦).
     */
    public function getCountryFlagAttribute(): string
    {
        return $this->timezone === 'Asia/Riyadh' ? '🇸🇦' : '🇮🇩';
    }

    /**
     * Nama negara cabang.
     */
    public function getCountryNameAttribute(): string
    {
        return $this->timezone === 'Asia/Riyadh' ? __('Arab Saudi') : __('Indonesia');
    }

    /**
     * URL Google Maps koordinat kantor.
     */
    public function getGoogleMapsUrlAttribute(): ?string
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }
        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    /**
     * Cek apakah cabang sudah memiliki titik koordinat GPS valid.
     */
    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }
}
