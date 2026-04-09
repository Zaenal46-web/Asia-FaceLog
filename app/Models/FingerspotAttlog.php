<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FingerspotAttlog extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'pin',
        'device_sn',
        'scan_time',
        'verify_mode',
        'status_scan',
        'photo_url',
        'raw',
    ];

    protected $casts = [
        'raw' => 'array',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(FingerspotDevice::class, 'device_id');
    }
}