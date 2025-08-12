<?php

namespace App\Filament\Resources\PenggunaanTeknisiResource\Pages;

use App\Filament\Resources\PenggunaanTeknisiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPenggunaanTeknisis extends ListRecords
{
    protected static string $resource = PenggunaanTeknisiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
