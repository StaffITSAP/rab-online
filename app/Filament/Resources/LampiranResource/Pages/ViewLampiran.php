<?php

namespace App\Filament\Resources\LampiranResource\Pages;

use App\Filament\Resources\LampiranResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLampiran extends ViewRecord
{
    protected static string $resource = LampiranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
