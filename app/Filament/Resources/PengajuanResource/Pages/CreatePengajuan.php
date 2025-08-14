<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use App\Models\Lampiran;
use App\Models\PengajuanMarcommKebutuhan;
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
        $formData = $this->data;

        Lampiran::updateOrCreate(
            ['pengajuan_id' => $pengajuan->id],
            [
                'lampiran_asset' => $formData['lampiran_asset'] ?? false,
                'lampiran_dinas' => $formData['lampiran_dinas'] ?? false,
                'lampiran_marcomm_promosi' => $formData['lampiran_marcomm_promosi'] ?? false,
                'lampiran_marcomm_kebutuhan' => $formData['lampiran_marcomm_kebutuhan'] ?? false,
            ]
        );

        // ====== PAKSA SIMPAN TOGGLE & TOTAL AMPLOP ======
        $amplopOn = !empty($formData['kebutuhan_amplop']);
        \App\Models\PengajuanMarcommKebutuhan::writeAmplopToggle($pengajuan->id, $amplopOn);
        \App\Models\PengajuanMarcommKebutuhan::syncTotalAmplop($pengajuan->id);

        $kartuOn = !empty($formData['kebutuhan_kartu']);
        \App\Models\PengajuanMarcommKebutuhan::writeKartuToggle($pengajuan->id, $kartuOn);

        $kemejaOn = !empty($formData['kebutuhan_kemeja']);
        \App\Models\PengajuanMarcommKebutuhan::writeKemejaToggle($pengajuan->id, $kemejaOn);

        $pusatOn = !empty($formData['tim_pusat']);
        \App\Models\PengajuanMarcommKegiatan::writePusatToggle($pengajuan->id, $pusatOn);

        $cabangOn = !empty($formData['tim_cabang']);
        \App\Models\PengajuanMarcommKegiatan::writeCabangToggle($pengajuan->id, $cabangOn);


        Log::info('Running afterCreate for pengajuan ID: ' . $pengajuan->id);

        $persetujuans = \App\Models\Persetujuan::with(['pengajuanApprovers.approver.roles'])
            ->where('user_id', $pengajuan->user_id)
            ->get();

        Log::info('Jumlah persetujuan ditemukan: ' . $persetujuans->count());

        foreach ($persetujuans as $persetujuan) {
            $skipTeknisi        = !($pengajuan->menggunakan_teknisi && $persetujuan->menggunakan_teknisi);
            $skipAssetTeknisi   = !($pengajuan->asset_teknisi && $persetujuan->asset_teknisi);

            // --- Perbaiki logika pengiriman ---
            $adaPengajuanPengiriman    = $pengajuan->use_pengiriman || $pengajuan->use_car;
            $adaPersetujuanPengiriman  = $persetujuan->use_pengiriman || $persetujuan->use_car;
            $skipPengiriman = !($adaPengajuanPengiriman && $adaPersetujuanPengiriman);

            foreach ($persetujuan->pengajuanApprovers as $approver) {
                $user = $approver->approver;
                if (!$user) continue;

                $roleNames = $user->getRoleNames();

                $isKoordinatorTeknisi = $roleNames->contains('koordinator teknisi');
                $isKoordinatorGudang  = $roleNames->contains('koordinator gudang');
                $isManager            = $roleNames->contains('manager');
                $isDirektur           = $roleNames->contains('direktur');
                $isOwner              = $roleNames->contains('owner');
                $isRt                 = $roleNames->contains('rt');

                // ❌ Skip jika kondisi tidak memenuhi
                if ($isKoordinatorTeknisi && $skipTeknisi) {
                    Log::info("❌ Skip Koordinator Teknisi: user_id {$user->id}");
                    continue;
                }

                if ($isRt && $skipAssetTeknisi) {
                    Log::info("❌ Skip RT (Asset Teknisi): user_id {$user->id}");
                    continue;
                }

                if ($isKoordinatorGudang && $skipPengiriman) {
                    Log::info("❌ Skip Koordinator Gudang (Pengiriman): user_id {$user->id}");
                    continue;
                }

                // -------------------
                // LOGIKA MANAGER FIX
                if ($isManager) {
                    if ($persetujuan->use_manager) {
                        if ($pengajuan->total_biaya < 1000000) {
                            Log::info("❌ Skip Manager: user_id {$user->id} (use_manager = true, nominal < 1jt)");
                            continue;
                        }
                    }
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

        // setelah form & RELATIONSHIPS tersimpan
        $total = $this->record->calculateTotalBiaya();
        $this->record->updateQuietly(['total_biaya' => $total]);

        // refresh state supaya field di UI ikut angka baru
        $this->fillForm();
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
