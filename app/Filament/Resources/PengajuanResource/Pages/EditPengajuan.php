<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengajuan extends EditRecord
{
    protected static string $resource = PengajuanResource::class;

    public bool $isReadOnly = false;

    public function mount($record): void
    {
        parent::mount($record);

        $user = auth()->user();
        $record = $this->getRecord();

        // Superadmin bisa edit semua
        if ($user && $user->hasRole('superadmin')) {
            $this->isReadOnly = false;
            return;
        }

        // Kalau bukan owner atau status selesai, readonly
        if ($record->user_id !== $user->id || $record->status === 'selesai') {
            $this->isReadOnly = true;
        }
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getFormActions(): array
    {
        if ($this->isReadOnly) {
            return [];
        }
        return parent::getFormActions();
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $pengajuan = $this->record;
        $lampiran = $pengajuan->lampiran;

        $data['lampiran_asset'] = $lampiran?->lampiran_asset ?? false;
        $data['lampiran_dinas'] = $lampiran?->lampiran_dinas ?? false;
        $data['lampiran_marcomm_promosi'] = $lampiran?->lampiran_marcomm_promosi ?? false;
        // ==== Prefill toggle kebutuhan_amplop dari DB ====
        $data['kebutuhan_amplop'] = (bool) \App\Models\PengajuanMarcommKebutuhan::where('pengajuan_id', $pengajuan->id)
            ->orderBy('id')
            ->value('kebutuhan_amplop');
        $data['kebutuhan_kartu'] = (bool) \App\Models\PengajuanMarcommKebutuhan::where('pengajuan_id', $pengajuan->id)
            ->orderBy('id')
            ->value('kebutuhan_kartu');
        $data['kebutuhan_kemeja'] = (bool) \App\Models\PengajuanMarcommKebutuhan::where('pengajuan_id', $pengajuan->id)
            ->orderBy('id')
            ->value('kebutuhan_kemeja');

        $data['tim_pusat'] = (bool) \App\Models\PengajuanMarcommKegiatan::where('pengajuan_id', $pengajuan->id)
            ->orderBy('id')
            ->value('tim_pusat');
        $data['tim_cabang'] = (bool) \App\Models\PengajuanMarcommKegiatan::where('pengajuan_id', $pengajuan->id)
            ->orderBy('id')
            ->value('tim_cabang');

        return $data;
    }

    protected function afterSave(): void
    {
        $pengajuan = $this->record;
        $formData = $this->data;

        // Update Lampiran (seperti afterCreate)
        $pengajuan->lampiran()->updateOrCreate(
            ['pengajuan_id' => $pengajuan->id],
            [
                'lampiran_asset' => $formData['lampiran_asset'] ?? false,
                'lampiran_dinas' => $formData['lampiran_dinas'] ?? false,
                'lampiran_marcomm_promosi' => $formData['lampiran_marcomm_promosi'] ?? false,
                'lampiran_marcomm_kebutuhan' => $formData['lampiran_marcomm_kebutuhan'] ?? false,
            ]
        );
        // ==== Tulis ulang toggle & total amplop ====
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
        // Hapus status existing dulu (hati-hati jika pengen preserve approval sebelumnya)
        \App\Models\PengajuanStatus::where('pengajuan_id', $pengajuan->id)->delete();

        // Logic generate ulang PengajuanStatus
        $persetujuans = \App\Models\Persetujuan::with(['pengajuanApprovers.approver.roles'])
            ->where('user_id', $pengajuan->user_id)
            ->get();

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
                $isRt                 = $roleNames->contains('rt');
                $isKoordinatorGudang  = $roleNames->contains('koordinator gudang');
                $isManager            = $roleNames->contains('manager');
                $isDirektur           = $roleNames->contains('direktur');
                $isOwner              = $roleNames->contains('owner');

                // ❌ Skip koordinator teknisi jika tidak butuh teknisi
                if ($isKoordinatorTeknisi && $skipTeknisi) {
                    continue;
                }
                // ❌ Skip RT jika tidak butuh asset teknisi
                if ($isRt && $skipAssetTeknisi) {
                    continue;
                }
                // ❌ Skip koordinator gudang jika tidak butuh pengiriman
                if ($isKoordinatorGudang && $skipPengiriman) {
                    continue;
                }

                // Manager logic
                if ($isManager) {
                    if ($persetujuan->use_manager) {
                        if ($pengajuan->total_biaya < 1000000) {
                            continue;
                        }
                    }
                }

                $autoApprove    = false;
                $autoApproveBy  = null;

                if ($isDirektur && $persetujuan->use_direktur) {
                    $autoApprove   = true;
                    $autoApproveBy = 'direktur';
                }
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
            }
        }

        $total = $this->record->calculateTotalBiaya();
        $this->record->updateQuietly(['total_biaya' => $total]);

        // refresh form agar nilai tampil sesuai DB
        $this->fillForm();
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
