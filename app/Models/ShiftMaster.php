<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShiftMaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kode',
        'jam_masuk',
        'jam_pulang',
        'lintas_hari',
        'sabtu_aktif',
        'minggu_aktif',
        'is_active',
    ];

    protected $casts = [
        'lintas_hari' => 'boolean',
        'sabtu_aktif' => 'boolean',
        'minggu_aktif' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function kategoriShifts(): HasMany
    {
        return $this->hasMany(KategoriShift::class);
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