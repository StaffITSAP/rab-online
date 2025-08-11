<?php

namespace App\Filament\Resources\LampiranKebutuhanResource\Pages;

use App\Filament\Resources\LampiranKebutuhanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLampiranKebutuhan extends EditRecord
{
    protected static string $resource = LampiranKebutuhanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
