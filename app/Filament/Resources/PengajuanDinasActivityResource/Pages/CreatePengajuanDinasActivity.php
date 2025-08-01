<?php

namespace App\Filament\Resources\PengajuanDinasActivityResource\Pages;

use App\Filament\Resources\PengajuanDinasActivityResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePengajuanDinasActivity extends CreateRecord
{
    protected static string $resource = PengajuanDinasActivityResource::class;
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
