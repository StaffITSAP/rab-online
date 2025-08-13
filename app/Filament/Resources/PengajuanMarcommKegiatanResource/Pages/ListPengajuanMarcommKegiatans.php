<?php

namespace App\Filament\Resources\PengajuanMarcommKegiatanResource\Pages;

use App\Filament\Resources\PengajuanMarcommKegiatanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanMarcommKegiatans extends ListRecords
{
    protected static string $resource = PengajuanMarcommKegiatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
