<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleCategoryScope extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_id',
        'kategori_karyawan_id',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function kategoriKaryawan(): BelongsTo
    {
        return $this->belongsTo(KategoriKaryawan::class);
    }
}