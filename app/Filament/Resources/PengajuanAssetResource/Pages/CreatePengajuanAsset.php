<?php

namespace App\Filament\Resources\PengajuanAssetResource\Pages;

use App\Filament\Resources\PengajuanAssetResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePengajuanAsset extends CreateRecord
{
    protected static string $resource = PengajuanAssetResource::class;
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
