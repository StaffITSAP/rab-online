<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['superadmin','owner', 'direktur', 'manager', 'hrd', 'koordinator teknisi', 'koordinator gudang', 'marcomm', 'rt', 'teknisi', 'spv', 'koordinator', 'user'];

        foreach ($roles as $roleName) {
            // Buat role jika belum ada
            $role = Role::firstOrCreate(['name' => $roleName]);

            // Buat user dengan email sesuai role
            $user = User::firstOrCreate([
                'email' => $roleName . '@example.com',
            ], [
                'name' => 'Nama ' . ($roleName),
                'username' => $roleName,
                'password' => Hash::make('R@bonlin3'),
                'is_active' => true,
            ]);

            // Assign role ke user (manual pivot table)
            $user->roles()->syncWithoutDetaching([$role->id]);
        }
    }
}
