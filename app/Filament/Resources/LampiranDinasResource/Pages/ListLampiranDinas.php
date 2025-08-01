<?php

namespace App\Filament\Resources\LampiranDinasResource\Pages;

use App\Filament\Resources\LampiranDinasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLampiranDinas extends ListRecords
{
    protected static string $resource = LampiranDinasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
