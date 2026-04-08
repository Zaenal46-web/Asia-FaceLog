<?php

namespace App\Support\Filters;

use App\Services\Attendance\KategoriScopeService;
use Illuminate\Database\Eloquent\Builder;


trait FiltersByKategoriScope
{
    protected function applyKategoriScope(Builder $query, string $column = 'kategori_karyawan_id'): Builder
    {
        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperadmin()) {
            return $query;
        }

        $scopeService = app(KategoriScopeService::class);
        $allowedIds = $scopeService->getUserAllowedKategoriIds($user);

        if (empty($allowedIds)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($column, $allowedIds);
    }
}