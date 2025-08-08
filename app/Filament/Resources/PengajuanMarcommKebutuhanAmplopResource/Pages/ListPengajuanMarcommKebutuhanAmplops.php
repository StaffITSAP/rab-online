<?php

namespace App\Filament\Resources\PengajuanMarcommKebutuhanAmplopResource\Pages;

use App\Filament\Resources\PengajuanMarcommKebutuhanAmplopResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanMarcommKebutuhanAmplops extends ListRecords
{
    protected static string $resource = PengajuanMarcommKebutuhanAmplopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
