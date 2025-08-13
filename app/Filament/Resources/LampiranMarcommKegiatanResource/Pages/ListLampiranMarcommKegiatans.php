<?php

namespace App\Filament\Resources\LampiranMarcommKegiatanResource\Pages;

use App\Filament\Resources\LampiranMarcommKegiatanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLampiranMarcommKegiatans extends ListRecords
{
    protected static string $resource = LampiranMarcommKegiatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
