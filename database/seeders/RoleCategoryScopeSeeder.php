<?php

namespace Database\Seeders;

use App\Models\KategoriKaryawan;
use App\Models\Role;
use App\Models\RoleCategoryScope;
use Illuminate\Database\Seeder;

class RoleCategoryScopeSeeder extends Seeder
{
    public function run(): void
    {
        $roleAsia = Role::where('code', 'hrd_asia')->first();
        $roleOutsourcing = Role::where('code', 'hrd_outsourcing')->first();

        $kategoriAsia = KategoriKaryawan::where('kode', 'ASIA')->first();
        $kategoriOutsourcing = KategoriKaryawan::where('kode', 'OUTSOURCING')->first();

        if ($roleAsia && $kategoriAsia) {
            RoleCategoryScope::updateOrCreate([
                'role_id' => $roleAsia->id,
                'kategori_karyawan_id' => $kategoriAsia->id,
            ]);
        }

        if ($roleOutsourcing && $kategoriOutsourcing) {
            RoleCategoryScope::updateOrCreate([
                'role_id' => $roleOutsourcing->id,
                'kategori_karyawan_id' => $kategoriOutsourcing->id,
            ]);
        }
    }
}