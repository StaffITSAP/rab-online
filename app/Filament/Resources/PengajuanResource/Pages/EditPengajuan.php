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

        return $data;
    }
}
