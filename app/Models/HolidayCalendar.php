<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HolidayCalendar extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'tipe',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_active' => 'boolean',
    ];

    public function categories(): HasMany
    {
        return $this->hasMany(HolidayCalendarCategory::class);
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