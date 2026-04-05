<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FingerspotWebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'event_type',
        'payload_json',
        'status',
        'message',
        'received_at',
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(FingerspotDevice::class, 'device_id');
    }
}