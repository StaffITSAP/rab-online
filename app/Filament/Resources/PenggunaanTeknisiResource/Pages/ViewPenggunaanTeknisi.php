<?php

namespace App\Filament\Resources\PenggunaanTeknisiResource\Pages;

use App\Filament\Resources\PenggunaanTeknisiResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPenggunaanTeknisi extends ViewRecord
{
    protected static string $resource = PenggunaanTeknisiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
