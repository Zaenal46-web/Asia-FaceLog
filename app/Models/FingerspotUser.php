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

    public function getRawDataAttribute(): array
    {
        if (!$this->raw_json) {
            return [];
        }

        if (is_array($this->raw_json)) {
            return $this->raw_json;
        }

        $decoded = json_decode($this->raw_json, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function getTemplateAttribute(): ?string
    {
        return $this->raw_data['template'] ?? null;
    }

    public function getApiNameAttribute(): ?string
    {
        return $this->raw_data['name'] ?? $this->nama;
    }

    public function getApiPrivilegeAttribute(): ?string
    {
        $value = $this->raw_data['privilege'] ?? $this->privilege;
        return $value === null ? null : (string) $value;
    }

    public function getApiPasswordAttribute(): ?string
    {
        return $this->raw_data['password'] ?? $this->password;
    }

    public function getApiRfidAttribute(): ?string
    {
        return $this->raw_data['rfid'] ?? $this->rfid;
    }

    public function getApiFaceAttribute(): string
    {
        return (string) ($this->raw_data['face'] ?? $this->face_template_count ?? 0);
    }

    public function getApiFingerAttribute(): string
    {
        return (string) ($this->raw_data['finger'] ?? $this->finger_template_count ?? 0);
    }

    public function getApiVeinAttribute(): string
    {
        return (string) ($this->raw_data['vein'] ?? $this->vein_template_count ?? 0);
    }
}