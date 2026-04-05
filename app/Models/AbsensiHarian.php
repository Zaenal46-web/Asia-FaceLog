<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AbsensiHarian extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'karyawan_id',
        'device_id',
        'kategori_karyawan_id',
        'kategori_shift_id',
        'shift_master_id',
        'jam_masuk',
        'jam_pulang',
        'status_hadir',
        'status_telat',
        'menit_telat',
        'status_pulang_cepat',
        'menit_pulang_cepat',
        'status_lembur',
        'menit_lembur',
        'total_menit_kerja',
        'total_menit_istirahat',
        'first_scan_at',
        'last_scan_at',
        'scan_count',
        'source',
        'is_manual',
        'is_holiday',
        'holiday_calendar_id',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'status_telat' => 'boolean',
        'status_pulang_cepat' => 'boolean',
        'status_lembur' => 'boolean',
        'first_scan_at' => 'datetime',
        'last_scan_at' => 'datetime',
        'is_manual' => 'boolean',
        'is_holiday' => 'boolean',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(FingerspotDevice::class, 'device_id');
    }

    public function kategoriKaryawan(): BelongsTo
    {
        return $this->belongsTo(KategoriKaryawan::class);
    }

    public function kategoriShift(): BelongsTo
    {
        return $this->belongsTo(KategoriShift::class);
    }

    public function shiftMaster(): BelongsTo
    {
        return $this->belongsTo(ShiftMaster::class);
    }

    public function holidayCalendar(): BelongsTo
    {
        return $this->belongsTo(HolidayCalendar::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AbsensiHarianLog::class)->latest();
    }
}