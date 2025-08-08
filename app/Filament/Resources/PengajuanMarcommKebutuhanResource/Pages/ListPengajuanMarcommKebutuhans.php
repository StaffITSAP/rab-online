<?php

namespace App\Filament\Resources\PengajuanMarcommKebutuhanResource\Pages;

use App\Filament\Resources\PengajuanMarcommKebutuhanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanMarcommKebutuhans extends ListRecords
{
    protected static string $resource = PengajuanMarcommKebutuhanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
