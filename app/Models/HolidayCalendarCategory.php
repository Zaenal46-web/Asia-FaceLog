<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HolidayCalendarCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'holiday_calendar_id',
        'kategori_karyawan_id',
    ];

    public function holidayCalendar(): BelongsTo
    {
        return $this->belongsTo(HolidayCalendar::class);
    }

    public function kategoriKaryawan(): BelongsTo
    {
        return $this->belongsTo(KategoriKaryawan::class);
    }
}