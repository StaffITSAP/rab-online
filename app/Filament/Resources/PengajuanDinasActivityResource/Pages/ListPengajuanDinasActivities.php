<?php

namespace App\Filament\Resources\PengajuanDinasActivityResource\Pages;

use App\Filament\Resources\PengajuanDinasActivityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanDinasActivities extends ListRecords
{
    protected static string $resource = PengajuanDinasActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
