<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FingerspotPushLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'pin',
        'action',
        'payload_json',
        'status',
        'response_message',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(FingerspotDevice::class, 'device_id');
    }
}