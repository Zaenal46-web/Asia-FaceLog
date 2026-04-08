<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserAwalSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = Role::where('code', 'superadmin')->first();

        if (! $superadmin) {
            return;
        }

        User::updateOrCreate(
            ['email' => 'admin@facelog.test'],
            [
                'name' => 'Superadmin FaceLog',
                'password' => Hash::make('password'),
                'role_id' => $superadmin->id,
                'is_active' => true,
            ]
        );
    }
}