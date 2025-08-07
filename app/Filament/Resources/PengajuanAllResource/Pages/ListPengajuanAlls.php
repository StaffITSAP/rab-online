<?php

namespace App\Filament\Resources\PengajuanAllResource\Pages;

use App\Filament\Resources\PengajuanAllResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanAlls extends ListRecords
{
    protected static string $resource = PengajuanAllResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
