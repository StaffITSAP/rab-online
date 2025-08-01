<?php

namespace App\Filament\Resources\PengajuanDinasPersonilResource\Pages;

use App\Filament\Resources\PengajuanDinasPersonilResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePengajuanDinasPersonil extends CreateRecord
{
    protected static string $resource = PengajuanDinasPersonilResource::class;
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
