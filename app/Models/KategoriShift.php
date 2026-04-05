<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'kategori_karyawan_id',
        'shift_master_id',
        'nama_rule',
        'prioritas',
        'is_default',
        'lintas_hari',
        'window_masuk_mulai_menit',
        'window_masuk_selesai_menit',
        'window_pulang_mulai_menit',
        'window_pulang_selesai_menit',
        'toleransi_telat_menit',
        'toleransi_pulang_cepat_menit',
        'toleransi_lembur_menit',
        'istirahat_aktif',
        'istirahat_otomatis_potong',
        'menit_istirahat_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'lintas_hari' => 'boolean',
        'istirahat_aktif' => 'boolean',
        'istirahat_otomatis_potong' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function kategoriKaryawan(): BelongsTo
    {
        return $this->belongsTo(KategoriKaryawan::class);
    }

    public function shiftMaster(): BelongsTo
    {
        return $this->belongsTo(ShiftMaster::class);
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(KategoriShiftJadwal::class)->orderBy('hari_ke');
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