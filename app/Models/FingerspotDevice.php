<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FingerspotDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'serial_number',
        'cloud_id',
        'lokasi',
        'timezone',
        'ip_address',
        'is_active',
        'last_seen_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    public function karyawans(): HasMany
    {
        return $this->hasMany(Karyawan::class, 'device_id');
    }

    public function fingerspotUsers(): HasMany
    {
        return $this->hasMany(FingerspotUser::class, 'device_id');
    }

    public function attlogs(): HasMany
    {
        return $this->hasMany(FingerspotAttlog::class, 'device_id');
    }

    public function pushLogs(): HasMany
    {
        return $this->hasMany(FingerspotPushLog::class, 'device_id');
    }

    public function webhookLogs(): HasMany
    {
        return $this->hasMany(FingerspotWebhookLog::class, 'device_id');
    }

    public function absensiHarians(): HasMany
    {
        return $this->hasMany(AbsensiHarian::class, 'device_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}