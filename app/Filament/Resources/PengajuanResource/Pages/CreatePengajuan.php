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

        $persetujuans = \App\Models\Persetujuan::with(['pengajuanApprovers.approver.roles'])
            ->where('user_id', $pengajuan->user_id)
            ->get();

        Log::info('Jumlah persetujuan ditemukan: ' . $persetujuans->count());

        foreach ($persetujuans as $persetujuan) {
            $skipTeknisi    = !($pengajuan->menggunakan_teknisi && $persetujuan->menggunakan_teknisi);
            $skipPengiriman = !($pengajuan->use_pengiriman && $persetujuan->use_pengiriman);
            $skipManager    = !($persetujuan->use_manager && $pengajuan->total_biaya >= 1000000);

            foreach ($persetujuan->pengajuanApprovers as $approver) {
                $user = $approver->approver;
                if (!$user) continue;

                $roleNames = $user->getRoleNames();

                $isKoordinatorTeknisi = $roleNames->contains('koordinator teknisi');
                $isKoordinatorGudang  = $roleNames->contains('koordinator gudang');
                $isManager            = $roleNames->contains('manager');
                $isDirektur           = $roleNames->contains('direktur');
                $isOwner              = $roleNames->contains('owner');

                // ❌ Skip jika kondisi tidak memenuhi
                if ($isKoordinatorTeknisi && $skipTeknisi) {
                    Log::info("❌ Skip Koordinator Teknisi: user_id {$user->id}");
                    continue;
                }

                if ($isKoordinatorGudang && $skipPengiriman) {
                    Log::info("❌ Skip Koordinator Gudang (Pengiriman): user_id {$user->id}");
                    continue;
                }

                if ($isManager && $skipManager) {
                    Log::info("❌ Skip Manager: user_id {$user->id}");
                    continue;
                }

                $autoApprove    = false;
                $autoApproveBy  = null;

                // ✅ Auto approve direktur (jika aktif)
                if ($isDirektur && $persetujuan->use_direktur) {
                    $autoApprove   = true;
                    $autoApproveBy = 'direktur';
                }

                // ✅ Auto approve owner (jika aktif)
                if ($isOwner && $persetujuan->use_owner) {
                    $autoApprove   = true;
                    $autoApproveBy = 'owner';
                }

                \App\Models\PengajuanStatus::create([
                    'pengajuan_id'   => $pengajuan->id,
                    'persetujuan_id' => $persetujuan->id,
                    'user_id'        => $user->id,
                    'is_approved'    => $autoApprove ? true : null,
                    'approved_at'    => $autoApprove ? now() : null,
                ]);

                Log::info("✅ Disimpan: user_id {$user->id}" . ($autoApprove ? " (auto approve {$autoApproveBy})" : ''));
            }
        }
    }
}
