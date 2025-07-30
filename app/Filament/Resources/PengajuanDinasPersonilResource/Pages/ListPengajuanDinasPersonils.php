<?php

namespace App\Filament\Resources\PengajuanDinasPersonilResource\Pages;

use App\Filament\Resources\PengajuanDinasPersonilResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanDinasPersonils extends ListRecords
{
    protected static string $resource = PengajuanDinasPersonilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
