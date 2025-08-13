<?php

namespace App\Filament\Resources\LampiranMarcommKegiatanCabangResource\Pages;

use App\Filament\Resources\LampiranMarcommKegiatanCabangResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLampiranMarcommKegiatanCabang extends EditRecord
{
    protected static string $resource = LampiranMarcommKegiatanCabangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
