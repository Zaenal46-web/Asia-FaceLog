<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Superadmin',
                'code' => 'superadmin',
                'description' => 'Akses penuh seluruh sistem',
                'is_active' => true,
            ],
            [
                'name' => 'HRD Asia',
                'code' => 'hrd_asia',
                'description' => 'Akses data kategori Asia dan turunannya',
                'is_active' => true,
            ],
            [
                'name' => 'HRD Outsourcing',
                'code' => 'hrd_outsourcing',
                'description' => 'Akses data kategori Outsourcing dan turunannya',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['code' => $role['code']],
                $role
            );
        }
    }
}