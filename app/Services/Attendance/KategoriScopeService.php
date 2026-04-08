<?php

namespace App\Services\Attendance;

use App\Models\KategoriKaryawan;
use App\Models\User;

class KategoriScopeService
{
    public function getDescendantIds(int $rootId): array
    {
        $all = KategoriKaryawan::select('id', 'parent_id')->get();

        $childrenMap = [];
        foreach ($all as $item) {
            $childrenMap[$item->parent_id ?? 0][] = $item->id;
        }

        $result = [];
        $stack = [$rootId];

        while (! empty($stack)) {
            $current = array_pop($stack);

            if (in_array($current, $result, true)) {
                continue;
            }

            $result[] = $current;

            foreach ($childrenMap[$current] ?? [] as $childId) {
                $stack[] = $childId;
            }
        }

        return $result;
    }

    public function getUserAllowedKategoriIds(User $user): array
    {
        if ($user->isSuperadmin()) {
            return KategoriKaryawan::pluck('id')->all();
        }

        $role = $user->role;
        if (! $role) {
            return [];
        }

        $rootIds = $role->categoryScopes()->pluck('kategori_karyawan_id')->all();

        $allowed = [];
        foreach ($rootIds as $rootId) {
            $allowed = array_merge($allowed, $this->getDescendantIds((int) $rootId));
        }

        return array_values(array_unique($allowed));
    }

    public function userCanAccessKategori(User $user, ?int $kategoriId): bool
    {
        if ($user->isSuperadmin()) {
            return true;
        }

        if (! $kategoriId) {
            return false;
        }

        return in_array($kategoriId, $this->getUserAllowedKategoriIds($user), true);
    }
}