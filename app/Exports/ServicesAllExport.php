<?php

namespace App\Exports;

use App\Models\Service;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ServicesAllExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Service::with(['user', 'stagingLogs'])->get();
    }

    public function headings(): array
    {
        return [
            'Nama Pemohon',
            'ID Paket',
            'Nama Dinas',
            'Kontak',
            'No Telepon',
            'Kerusakan',
            'Nama Barang',
            'No Serial',
            'Status Garansi',
            'No SO',
            'Status Staging',
            'Keterangan Staging',
            'Tanggal Dibuat',
            'Tanggal Diupdate'
        ];
    }

    public function map($service): array
    {
        return [
            $service->user->name ?? 'Tidak diketahui', // Kolom nama pemohon
            $service->id_paket,
            $service->nama_dinas,
            $service->kontak,
            $service->no_telepon,
            $service->kerusakan,
            $service->nama_barang,
            $service->noserial,
            $service->masih_garansi == 'Y' ? 'Masih Garansi' : 'Tidak Garansi',
            $service->nomer_so,
            $service->staging->label(),
            $service->keterangan_staging,
            $service->created_at->format('d/m/Y H:i'),
            $service->updated_at->format('d/m/Y H:i')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header row bold dengan background
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE6E6E6']
                ]
            ],

            // Set column widths
            'A' => ['width' => 20], // Nama Pemohon
            'B' => ['width' => 15], // ID Paket
            'C' => ['width' => 20], // Nama Dinas
            'D' => ['width' => 20], // Kontak
            'E' => ['width' => 15], // No Telepon
            'F' => ['width' => 30], // Kerusakan
            'G' => ['width' => 20], // Nama Barang
            'H' => ['width' => 15], // No Serial
            'I' => ['width' => 15], // Status Garansi
            'J' => ['width' => 15], // No SO
            'K' => ['width' => 15], // Status Staging
            'L' => ['width' => 30], // Keterangan Staging
            'M' => ['width' => 20], // Tanggal Dibuat
            'N' => ['width' => 20], // Tanggal Diupdate
        ];
    }
}
