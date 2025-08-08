<?php

namespace App\Filament\Resources\PengajuanMarcommKebutuhanResource\Pages;

use App\Filament\Resources\PengajuanMarcommKebutuhanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengajuanMarcommKebutuhan extends EditRecord
{
    protected static string $resource = PengajuanMarcommKebutuhanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
