<?php

namespace App\Filament\Resources\PengajuanMarcommKegiatanResource\Pages;

use App\Filament\Resources\PengajuanMarcommKegiatanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengajuanMarcommKegiatan extends EditRecord
{
    protected static string $resource = PengajuanMarcommKegiatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
