<?php

namespace App\Filament\Resources\PengajuanDinasActivityResource\Pages;

use App\Filament\Resources\PengajuanDinasActivityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengajuanDinasActivity extends EditRecord
{
    protected static string $resource = PengajuanDinasActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
