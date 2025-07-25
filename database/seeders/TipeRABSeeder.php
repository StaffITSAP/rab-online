<?php

namespace Database\Seeders;

use App\Models\TipeRAB;
use Illuminate\Database\Seeder;

class TipeRABSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => 'AI', 'nama' => 'RAB Asset/Inventaris'],
            ['kode' => 'PD', 'nama' => 'RAB Perjalanan Dinas'],
            ['kode' => 'MP', 'nama' => 'RAB Marcomm Promosi'],
            ['kode' => 'MK', 'nama' => 'RAB Marcomm Kegiatan'],
            ['kode' => 'ME', 'nama' => 'RAB Marcomm Event'],
        ];

        foreach ($data as $tipe) {
            TipeRAB::create($tipe);
        }
    }
}
