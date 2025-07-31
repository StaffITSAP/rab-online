<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'superadmin',
            'direktur',
            'manager',
            'hrd',
            'koordinator teknisi',
            'koordinator gudang',
            'marcomm',
            'rt',
            'teknisi',
            'spv',
            'koordinator',
            'user',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }
}
