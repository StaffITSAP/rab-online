<?php

namespace App\Filament\Resources\LampiranDinasResource\Pages;

use App\Filament\Resources\LampiranDinasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLampiranDinas extends EditRecord
{
    protected static string $resource = LampiranDinasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
