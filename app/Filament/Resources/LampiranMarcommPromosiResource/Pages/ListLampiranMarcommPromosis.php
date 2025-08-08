<?php

namespace App\Filament\Resources\LampiranMarcommPromosiResource\Pages;

use App\Filament\Resources\LampiranMarcommPromosiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLampiranMarcommPromosis extends ListRecords
{
    protected static string $resource = LampiranMarcommPromosiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
