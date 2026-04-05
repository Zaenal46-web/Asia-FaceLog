<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Karyawan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'pin_fingerspot',
        'kategori_karyawan_id',
        'device_id',
        'jabatan',
        'tanggal_masuk',
        'status_kerja',
        'is_active',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'is_active' => 'boolean',
    ];

    public function kategoriKaryawan(): BelongsTo
    {
        return $this->belongsTo(KategoriKaryawan::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(FingerspotDevice::class, 'device_id');
    }

    public function absensiHarians(): HasMany
    {
        return $this->hasMany(AbsensiHarian::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}