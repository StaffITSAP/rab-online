<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\PengajuanStatus;
use App\Models\Persetujuan;
use App\Models\PersetujuanApprover;
use Illuminate\Support\Facades\Log;


class CreatePengajuan extends CreateRecord
{
    protected static string $resource = PengajuanResource::class;
    protected function afterCreate(): void
    {
        $pengajuan = $this->record;

        Log::info('Running afterCreate for pengajuan ID: ' . $pengajuan->id);

        $persetujuans = \App\Models\Persetujuan::with(['pengajuanApprovers.approver'])
            ->where('user_id', $pengajuan->user_id)
            ->get();

        Log::info('Jumlah persetujuan ditemukan: ' . $persetujuans->count());

        foreach ($persetujuans as $persetujuan) {
            Log::info('DEBUG: pengajuan->menggunakan_teknisi = ' . var_export($pengajuan->menggunakan_teknisi, true));
            Log::info('DEBUG: persetujuan->menggunakan_teknisi = ' . var_export($persetujuan->menggunakan_teknisi, true));

            $skipTeknisi = !($pengajuan->menggunakan_teknisi && $persetujuan->menggunakan_teknisi);

            foreach ($persetujuan->pengajuanApprovers as $approver) {
                $user = $approver->approver;

                if (!$user) {
                    continue;
                }

                if ($user->id === $pengajuan->user_id) {
                    continue;
                }

                $roleNames = $user->getRoleNames();
                $isKoordinatorTeknisi = $roleNames->contains('koordinator teknisi');

                Log::info("Approver ID: {$user->id}, Role Names: " . $roleNames->implode(', '));

                if ($isKoordinatorTeknisi && $skipTeknisi) {
                    Log::info("Skip Koordinator Teknisi karena tidak menggunakan teknisi");
                    continue;
                }

                PengajuanStatus::create([
                    'pengajuan_id'   => $pengajuan->id,
                    'persetujuan_id' => $persetujuan->id,
                    'user_id'        => $user->id,
                ]);

                Log::info("✅ Disimpan ke pengajuan_statuses untuk user_id {$user->id}");
            }
        }
    }
}
