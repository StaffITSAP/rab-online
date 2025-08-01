<?php

namespace App\Filament\Resources\LampiranAssetResource\Pages;

use App\Filament\Resources\LampiranAssetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLampiranAsset extends EditRecord
{
    protected static string $resource = LampiranAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
