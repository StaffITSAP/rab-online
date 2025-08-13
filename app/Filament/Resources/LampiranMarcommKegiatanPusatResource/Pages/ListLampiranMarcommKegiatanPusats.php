<?php

namespace App\Filament\Resources\LampiranMarcommKegiatanPusatResource\Pages;

use App\Filament\Resources\LampiranMarcommKegiatanPusatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLampiranMarcommKegiatanPusats extends ListRecords
{
    protected static string $resource = LampiranMarcommKegiatanPusatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
