<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Divisi;

class DivisiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama' => 'Direktur'],
            ['nama' => 'Manager'],
            ['nama' => 'HRD'],
            ['nama' => 'HRDGA'],
            ['nama' => 'SPV'],
            ['nama' => 'Koordinator'],
            ['nama' => 'Teknisi'],
            ['nama' => 'IT'],
        ];

        foreach ($data as $item) {
            Divisi::create($item);
        }
    }
}
