<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsensiHarianLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'absensi_harian_id',
        'action',
        'notes',
        'payload_json',
    ];

    public function absensiHarian(): BelongsTo
    {
        return $this->belongsTo(AbsensiHarian::class);
    }
}