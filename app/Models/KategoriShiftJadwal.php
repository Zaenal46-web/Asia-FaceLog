<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KategoriShiftJadwal extends Model
{
    use HasFactory;

    protected $fillable = [
        'kategori_shift_id',
        'hari_ke',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function kategoriShift(): BelongsTo
    {
        return $this->belongsTo(KategoriShift::class);
    }
}