<?php

namespace App\Filament\Resources\PenggunaanTeknisiResource\Pages;

use App\Filament\Resources\PenggunaanTeknisiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPenggunaanTeknisi extends EditRecord
{
    protected static string $resource = PenggunaanTeknisiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
