<?php

namespace App\Filament\Resources\LampiranMarcommKegiatanCabangResource\Pages;

use App\Filament\Resources\LampiranMarcommKegiatanCabangResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLampiranMarcommKegiatanCabang extends ViewRecord
{
    protected static string $resource = LampiranMarcommKegiatanCabangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
