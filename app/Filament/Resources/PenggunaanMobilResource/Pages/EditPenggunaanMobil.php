<?php

namespace App\Filament\Resources\PenggunaanMobilResource\Pages;

use App\Filament\Resources\PenggunaanMobilResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPenggunaanMobil extends EditRecord
{
    protected static string $resource = PenggunaanMobilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
