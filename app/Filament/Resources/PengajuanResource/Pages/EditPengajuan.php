<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengajuan extends EditRecord
{
    protected static string $resource = PengajuanResource::class;

    public bool $isReadOnly = false;

    // GANTI: harus public
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
        $data['lampiran_dinas'] = $lampiran?->lampiran_asset ?? false;

        return $data;
    }
    protected function afterSave(): void
    {
        $pengajuan = $this->record;
        $formData = $this->data;

        // OPTIONAL: Update Lampiran (seperti afterCreate)
        $pengajuan->lampiran()->updateOrCreate(
            ['pengajuan_id' => $pengajuan->id],
            [
                'lampiran_asset' => $formData['lampiran_asset'] ?? false,
                'lampiran_dinas' => $formData['lampiran_dinas'] ?? false,
            ]
        );

        // Hapus status existing dulu (hati-hati jika pengen preserve approval sebelumnya)
        \App\Models\PengajuanStatus::where('pengajuan_id', $pengajuan->id)->delete();

        // Kopi logic dari afterCreate untuk generate ulang PengajuanStatus
        $persetujuans = \App\Models\Persetujuan::with(['pengajuanApprovers.approver.roles'])
            ->where('user_id', $pengajuan->user_id)
            ->get();

        foreach ($persetujuans as $persetujuan) {
            $skipTeknisi    = !($pengajuan->menggunakan_teknisi && $persetujuan->menggunakan_teknisi);
            $skipPengiriman = !(($pengajuan->use_pengiriman || $pengajuan->use_car) && $persetujuan->use_pengiriman || $persetujuan->use_car);

            foreach ($persetujuan->pengajuanApprovers as $approver) {
                $user = $approver->approver;
                if (!$user) continue;

                $roleNames = $user->getRoleNames();

                $isKoordinatorTeknisi = $roleNames->contains(function ($value) {
                    return $value === 'koordinator teknisi' || $value === 'rt';
                });

                $isKoordinatorGudang  = $roleNames->contains('koordinator gudang');
                $isManager            = $roleNames->contains('manager');
                $isDirektur           = $roleNames->contains('direktur');
                $isOwner              = $roleNames->contains('owner');

                // ❌ Skip jika kondisi tidak memenuhi
                if ($isKoordinatorTeknisi && $skipTeknisi) {
                    continue;
                }

                if ($isKoordinatorGudang && $skipPengiriman) {
                    continue;
                }

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
    }
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
