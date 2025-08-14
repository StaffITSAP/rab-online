<?php

namespace App\Filament\Resources\PengajuanAllResource\Pages;

use App\Filament\Resources\PengajuanAllResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengajuanAll extends EditRecord
{
    protected static string $resource = PengajuanAllResource::class;

    public bool $isReadOnly = false;

    public function mount($record): void
    {
        parent::mount($record);

        $user   = auth()->user();
        $record = $this->getRecord();

        // Superadmin bisa edit semua
        if ($user && $user->hasRole('superadmin')) {
            $this->isReadOnly = false;
            return;
        }

        // Jika status 'selesai' -> kunci
        if ($record->status === 'selesai') {
            $this->isReadOnly = true;
            return;
        }

        // Pemilik pengajuan boleh edit
        if ($record->user_id === $user->id) {
            $this->isReadOnly = false;
            return;
        }

        // Approver pengajuan boleh edit
        $isApprover = \App\Models\PengajuanStatus::where('pengajuan_id', $record->id)
            ->where('user_id', $user->id)
            ->exists();

        $this->isReadOnly = !$isApprover;
    }

    protected function getHeaderActions(): array
    {
        // Tampilkan Delete hanya jika tidak read-only
        return $this->isReadOnly ? [] : [ Actions\DeleteAction::make() ];
    }

    protected function getFormActions(): array
    {
        // Sembunyikan tombol Save bila read-only (form tetap tampil agar bisa dilihat)
        return $this->isReadOnly ? [] : parent::getFormActions();
    }

    /**
     * Prefill toggle/kolom dari relasi untuk ditampilkan di form.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $pengajuan = $this->record;
        $lampiran  = $pengajuan->lampiran;

        // Lampiran umum
        $data['lampiran_asset']             = $lampiran?->lampiran_asset ?? false;
        $data['lampiran_dinas']             = $lampiran?->lampiran_dinas ?? false;
        $data['lampiran_marcomm_promosi']   = $lampiran?->lampiran_marcomm_promosi ?? false;
        $data['lampiran_marcomm_kebutuhan'] = $lampiran?->lampiran_marcomm_kebutuhan ?? false;

        // Kebutuhan
        $data['kebutuhan_amplop'] = (bool) \App\Models\PengajuanMarcommKebutuhan::where('pengajuan_id', $pengajuan->id)
            ->orderBy('id')->value('kebutuhan_amplop');

        $data['kebutuhan_kartu'] = (bool) \App\Models\PengajuanMarcommKebutuhan::where('pengajuan_id', $pengajuan->id)
            ->orderBy('id')->value('kebutuhan_kartu');

        $data['kebutuhan_kemeja'] = (bool) \App\Models\PengajuanMarcommKebutuhan::where('pengajuan_id', $pengajuan->id)
            ->orderBy('id')->value('kebutuhan_kemeja');

        // Kegiatan
        $data['tim_pusat'] = (bool) \App\Models\PengajuanMarcommKegiatan::where('pengajuan_id', $pengajuan->id)
            ->orderBy('id')->value('tim_pusat');

        $data['tim_cabang'] = (bool) \App\Models\PengajuanMarcommKegiatan::where('pengajuan_id', $pengajuan->id)
            ->orderBy('id')->value('tim_cabang');

        return $data;
    }

    /**
     * Sinkronkan relasi & status setelah simpan.
     */
    protected function afterSave(): void
    {
        if ($this->isReadOnly) {
            return; // pengaman
        }

        $pengajuan = $this->record;
        $formData  = $this->data;

        // Update lampiran flags
        $pengajuan->lampiran()->updateOrCreate(
            ['pengajuan_id' => $pengajuan->id],
            [
                'lampiran_asset'             => $formData['lampiran_asset'] ?? false,
                'lampiran_dinas'             => $formData['lampiran_dinas'] ?? false,
                'lampiran_marcomm_promosi'   => $formData['lampiran_marcomm_promosi'] ?? false,
                'lampiran_marcomm_kebutuhan' => $formData['lampiran_marcomm_kebutuhan'] ?? false,
            ]
        );

        // Toggle kebutuhan
        \App\Models\PengajuanMarcommKebutuhan::writeAmplopToggle($pengajuan->id, !empty($formData['kebutuhan_amplop']));
        \App\Models\PengajuanMarcommKebutuhan::syncTotalAmplop($pengajuan->id);
        \App\Models\PengajuanMarcommKebutuhan::writeKartuToggle($pengajuan->id, !empty($formData['kebutuhan_kartu']));
        \App\Models\PengajuanMarcommKebutuhan::writeKemejaToggle($pengajuan->id, !empty($formData['kebutuhan_kemeja']));

        // Toggle kegiatan
        \App\Models\PengajuanMarcommKegiatan::writePusatToggle($pengajuan->id,  !empty($formData['tim_pusat']));
        \App\Models\PengajuanMarcommKegiatan::writeCabangToggle($pengajuan->id, !empty($formData['tim_cabang']));

        // Regenerate alur persetujuan (hapus lama)
        \App\Models\PengajuanStatus::where('pengajuan_id', $pengajuan->id)->delete();

        $persetujuans = \App\Models\Persetujuan::with(['pengajuanApprovers.approver.roles'])
            ->where('user_id', $pengajuan->user_id)
            ->get();

        foreach ($persetujuans as $persetujuan) {
            $skipTeknisi       = !($pengajuan->menggunakan_teknisi && $persetujuan->menggunakan_teknisi);
            $skipAssetTeknisi  = !($pengajuan->asset_teknisi && $persetujuan->asset_teknisi);
            $adaPengajuanKirim = $pengajuan->use_pengiriman || $pengajuan->use_car;
            $adaRuleKirim      = $persetujuan->use_pengiriman || $persetujuan->use_car;
            $skipPengiriman    = !($adaPengajuanKirim && $adaRuleKirim);

            foreach ($persetujuan->pengajuanApprovers as $approver) {
                $user = $approver->approver;
                if (!$user) continue;

                $roles = $user->getRoleNames();

                $isKoordinatorTeknisi = $roles->contains('koordinator teknisi');
                $isRt                 = $roles->contains('rt');
                $isKoordinatorGudang  = $roles->contains('koordinator gudang');
                $isManager            = $roles->contains('manager');
                $isDirektur           = $roles->contains('direktur');
                $isOwner              = $roles->contains('owner');

                if ($isKoordinatorTeknisi && $skipTeknisi)   continue;
                if ($isRt && $skipAssetTeknisi)               continue;
                if ($isKoordinatorGudang && $skipPengiriman) continue;

                if ($isManager && $persetujuan->use_manager && $pengajuan->total_biaya < 1_000_000) {
                    continue;
                }

                $autoApprove = false;
                if ($isDirektur && $persetujuan->use_direktur) $autoApprove = true;
                if ($isOwner && $persetujuan->use_owner)       $autoApprove = true;

                \App\Models\PengajuanStatus::create([
                    'pengajuan_id'   => $pengajuan->id,
                    'persetujuan_id' => $persetujuan->id,
                    'user_id'        => $user->id,
                    'is_approved'    => $autoApprove ? true : null,
                    'approved_at'    => $autoApprove ? now() : null,
                ]);
            }
        }

        // Hitung & set total
        $total = $this->record->calculateTotalBiaya();
        $this->record->updateQuietly(['total_biaya' => $total]);

        $this->fillForm();
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
