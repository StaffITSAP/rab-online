<?php

namespace App\Filament\Resources\LampiranResource\Pages;

use App\Filament\Resources\LampiranResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLampiran extends CreateRecord
{
    protected static string $resource = LampiranResource::class;
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
