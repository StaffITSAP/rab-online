<?php

namespace App\Filament\Resources\PengajuanDinasResource\Pages;

use App\Filament\Resources\PengajuanDinasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanDinas extends ListRecords
{
    protected static string $resource = PengajuanDinasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
