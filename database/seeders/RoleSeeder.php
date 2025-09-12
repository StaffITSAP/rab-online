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
            'owner',
            'direktur',
            'manager',
            'hrd',
            'koordinator teknisi',
            'koordinator gudang',
            'marcomm',
            'rt',
            'servis',
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
