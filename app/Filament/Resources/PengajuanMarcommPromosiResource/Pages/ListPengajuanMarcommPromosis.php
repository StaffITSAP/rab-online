<?php

namespace App\Filament\Resources\PengajuanMarcommPromosiResource\Pages;

use App\Filament\Resources\PengajuanMarcommPromosiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanMarcommPromosis extends ListRecords
{
    protected static string $resource = PengajuanMarcommPromosiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
