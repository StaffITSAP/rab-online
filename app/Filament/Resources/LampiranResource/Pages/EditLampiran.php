<?php

namespace App\Filament\Resources\LampiranResource\Pages;

use App\Filament\Resources\LampiranResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLampiran extends EditRecord
{
    protected static string $resource = LampiranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
