<?php

namespace App\Filament\Resources\LampiranMarcommKegiatanCabangResource\Pages;

use App\Filament\Resources\LampiranMarcommKegiatanCabangResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLampiranMarcommKegiatanCabangs extends ListRecords
{
    protected static string $resource = LampiranMarcommKegiatanCabangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
