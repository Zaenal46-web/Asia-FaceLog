<?php

namespace App\Services\Attendance;

use App\Models\KategoriKaryawan;

class KategoriScopeService
{
    public function getLineageIds(int $kategoriId): array
    {
        $ids = [];
        $current = KategoriKaryawan::find($kategoriId);

        while ($current) {
            $ids[] = $current->id;
            $current = $current->parent_id ? KategoriKaryawan::find($current->parent_id) : null;
        }

        return array_values(array_unique($ids));
    }
}