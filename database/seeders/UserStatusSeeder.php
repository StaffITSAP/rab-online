<?php

namespace Database\Seeders;

use App\Models\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserStatusSeeder extends Seeder
{
    public function run(): void
    {
        foreach (User::all() as $user) {
            UserStatus::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'is_active' => true,
                    'signature_path' => null,
                    'cabang_id' => null,
                    'divisi_id' => null,
                    'atasan_id' => null,
                ]
            );
        }
    }
}
