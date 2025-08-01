<?php

namespace App\Filament\Resources\LampiranAssetResource\Pages;

use App\Filament\Resources\LampiranAssetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLampiranAssets extends ListRecords
{
    protected static string $resource = LampiranAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
