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

        // Ambil semua persetujuan dan approvers-nya
        $persetujuans = Persetujuan::with('approvers')->get();

        foreach ($persetujuans as $persetujuan) {
            foreach ($persetujuan->approvers as $user) {
                // Cegah pengaju menyetujui sendiri
                if ($user->id !== $pengajuan->user_id) {
                    PengajuanStatus::create([
                        'pengajuan_id'    => $pengajuan->id,
                        'persetujuan_id'  => $persetujuan->id,
                        'user_id'         => $user->id,
                    ]);
                }
            }
        }
    }
}
