<?php

namespace Database\Seeders;

use App\Models\KategoriKaryawan;
use Illuminate\Database\Seeder;

class KategoriKaryawanSeeder extends Seeder
{
    public function run(): void
    {
        $asia = KategoriKaryawan::updateOrCreate(
            ['kode' => 'ASIA'],
            [
                'parent_id' => null,
                'nama' => 'Asia',
                'urutan' => 1,
                'is_active' => true,
            ]
        );

        $outsourcing = KategoriKaryawan::updateOrCreate(
            ['kode' => 'OUTSOURCING'],
            [
                'parent_id' => null,
                'nama' => 'Outsourcing',
                'urutan' => 2,
                'is_active' => true,
            ]
        );

        KategoriKaryawan::updateOrCreate(
            ['kode' => 'ASIA_UMUM'],
            [
                'parent_id' => $asia->id,
                'nama' => 'Asia Umum',
                'urutan' => 1,
                'is_active' => true,
            ]
        );

        KategoriKaryawan::updateOrCreate(
            ['kode' => 'OUTSOURCING_UMUM'],
            [
                'parent_id' => $outsourcing->id,
                'nama' => 'Outsourcing Umum',
                'urutan' => 1,
                'is_active' => true,
            ]
        );
    }
}