<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriKaryawan extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'nama',
        'kode',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('urutan');
    }

    public function karyawans(): HasMany
    {
        return $this->hasMany(Karyawan::class);
    }

    public function kategoriShifts(): HasMany
    {
        return $this->hasMany(KategoriShift::class);
    }

    public function holidayCalendarCategories(): HasMany
    {
        return $this->hasMany(HolidayCalendarCategory::class);
    }

    public function roleCategoryScopes(): HasMany
    {
        return $this->hasMany(RoleCategoryScope::class);
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