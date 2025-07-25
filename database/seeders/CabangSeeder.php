<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cabang;

class CabangSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => 'PUSAT', 'nama' => 'Semarang'],
            ['kode' => 'Jawa Tengah 1', 'nama' => 'Semarang'],
            ['kode' => 'Jawa Tengah 2', 'nama' => 'Semarang'],
            ['kode' => 'Jakarta', 'nama' => 'Jakarta'],
            ['kode' => 'Yogyakarta', 'nama' => 'Yogyakarta'],
            ['kode' => 'Bali', 'nama' => 'Denpasar'],
            ['kode' => 'Jawa Barat', 'nama' => 'Bandung'],
            ['kode' => 'Sumatera', 'nama' => 'Medan'],
            ['kode' => 'Kalimantan', 'nama' => 'Balikpapan'],
        ];

        foreach ($data as $item) {
            Cabang::firstOrCreate(['kode' => $item['kode']], $item);
        }
    }
}
