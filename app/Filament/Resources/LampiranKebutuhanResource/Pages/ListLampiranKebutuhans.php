<?php

namespace App\Filament\Resources\LampiranKebutuhanResource\Pages;

use App\Filament\Resources\LampiranKebutuhanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLampiranKebutuhans extends ListRecords
{
    protected static string $resource = LampiranKebutuhanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
