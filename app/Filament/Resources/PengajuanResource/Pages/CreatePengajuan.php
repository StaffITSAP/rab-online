<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\PengajuanStatus;
use App\Models\Persetujuan;
use App\Models\PersetujuanApprover;

class CreatePengajuan extends CreateRecord
{
    protected static string $resource = PengajuanResource::class;
    protected function afterCreate(): void
    {
        $pengajuan = $this->record;

        // Ambil persetujuan yang sesuai user pengaju
        $persetujuans = \App\Models\Persetujuan::with('pengajuanApprovers')
            ->where('user_id', $pengajuan->user_id)
            ->get();

        foreach ($persetujuans as $persetujuan) {
            foreach ($persetujuan->pengajuanApprovers as $approver) {
                // Lewati jika user menyetujui dirinya sendiri
                if ($approver->id === $pengajuan->user_id) {
                    continue;
                }

                \App\Models\PengajuanStatus::create([
                    'pengajuan_id'   => $pengajuan->id,
                    'persetujuan_id' => $persetujuan->id,
                    'user_id'        => $approver->id,
                ]);
            }
        }
    }
}
