<?php

namespace App\Filament\Resources\PengajuanMarcommKebutuhanKartuResource\Pages;

use App\Filament\Resources\PengajuanMarcommKebutuhanKartuResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanMarcommKebutuhanKartus extends ListRecords
{
    protected static string $resource = PengajuanMarcommKebutuhanKartuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
