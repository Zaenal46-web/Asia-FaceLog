<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FingerspotUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'pin',
        'nama',
        'privilege',
        'password',
        'rfid',
        'face_template_count',
        'finger_template_count',
        'vein_template_count',
        'raw_json',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(FingerspotDevice::class, 'device_id');
    }
}