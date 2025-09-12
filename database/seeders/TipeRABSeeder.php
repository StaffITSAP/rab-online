<?php

namespace Database\Seeders;

use App\Models\TipeRab;
use Illuminate\Database\Seeder;

class TipeRABSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => 'AI', 'nama' => 'RAB Asset/Inventaris'],
            ['kode' => 'PD', 'nama' => 'RAB Perjalanan Dinas'],
            ['kode' => 'MP', 'nama' => 'RAB Marcomm Event/Kegiatan'],
            ['kode' => 'MK', 'nama' => 'RAB Marcomm Promosi'],
            ['kode' => 'ME', 'nama' => 'RAB Marcomm Kebutuhan Pusat/Sales'],
            ['kode' => 'BS', 'nama' => 'RAB Biaya Service'],
        ];

        foreach ($data as $tipe) {
            TipeRab::create($tipe);
        }
    }
}
