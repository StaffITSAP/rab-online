<?php

namespace App\Filament\Resources\PengajuanAssetResource\Pages;

use App\Filament\Resources\PengajuanAssetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengajuanAsset extends EditRecord
{
    protected static string $resource = PengajuanAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
