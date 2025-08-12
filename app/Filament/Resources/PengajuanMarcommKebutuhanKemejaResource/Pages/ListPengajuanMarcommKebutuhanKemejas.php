<?php

namespace App\Filament\Resources\PengajuanMarcommKebutuhanKemejaResource\Pages;

use App\Filament\Resources\PengajuanMarcommKebutuhanKemejaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanMarcommKebutuhanKemejas extends ListRecords
{
    protected static string $resource = PengajuanMarcommKebutuhanKemejaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
