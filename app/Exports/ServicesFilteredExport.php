<?php

namespace App\Exports;

use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ServicesFilteredExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;
    protected string $search;

    public function __construct(array $filters = [], string $search = '')
    {
        $this->filters = $filters;
        $this->search  = $search;
    }

    public function collection()
    {
        Log::info('ServicesFilteredExport Parameters:', [
            'filters' => $this->filters,
            'search'  => $this->search,
        ]);

        $query = Service::query()->with(['user', 'stagingLogs']);

        // Global search
        if (!empty($this->search)) {
            $search = $this->search;
            $query->where(function (Builder $q) use ($search) {
                $q->where('id_paket', 'like', "%{$search}%")
                    ->orWhere('nama_dinas', 'like', "%{$search}%")
                    ->orWhere('kontak', 'like', "%{$search}%")
                    ->orWhere('nama_barang', 'like', "%{$search}%")
                    ->orWhere('noserial', 'like', "%{$search}%")
                    ->orWhere('nomer_so', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        // Filters (terima bentuk ['value'=>..] ATAU scalar)
        foreach ($this->filters as $name => $data) {
            $value = null;

            if (is_array($data) && array_key_exists('value', $data)) {
                $value = $data['value'];
            } elseif (is_scalar($data)) {
                $value = $data;
            }

            if ($value === null || $value === '' || $value === 'null') {
                continue;
            }

            switch ($name) {
                case 'staging':
                    $query->where('staging', $value);
                    break;

                case 'masih_garansi':
                    $query->where('masih_garansi', $value);
                    break;

                case 'user.name':
                case 'user_id':
                    $query->whereHas('user', fn($uq) => $uq->where('name', 'like', "%{$value}%"));
                    break;

                // Tambahkan mapping filter lain di sini bila ada di Table()
                default:
                    // Jika ingin, bisa tambahkan fallback kolom langsung:
                    // if (Schema::hasColumn('services', $name)) $query->where($name, $value);
                    break;
            }
        }

        $results = $query->get();

        Log::info('ServicesFilteredExport Results:', [
            'count'            => $results->count(),
            'filters_applied'  => $this->filters,
            'search_applied'   => $this->search,
        ]);

        return $results;
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
            'Tanggal Diupdate',
        ];
    }

    public function map($service): array
    {
        return [
            $service->user->name ?? 'Tidak diketahui',
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
            $service->created_at?->format('d/m/Y H:i'),
            $service->updated_at?->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE6E6E6'],
                ],
            ],
            'A' => ['width' => 20],
            'B' => ['width' => 15],
            'C' => ['width' => 20],
            'D' => ['width' => 20],
            'E' => ['width' => 15],
            'F' => ['width' => 30],
            'G' => ['width' => 20],
            'H' => ['width' => 15],
            'I' => ['width' => 15],
            'J' => ['width' => 15],
            'K' => ['width' => 15],
            'L' => ['width' => 30],
            'M' => ['width' => 20],
            'N' => ['width' => 20],
        ];
    }
}
