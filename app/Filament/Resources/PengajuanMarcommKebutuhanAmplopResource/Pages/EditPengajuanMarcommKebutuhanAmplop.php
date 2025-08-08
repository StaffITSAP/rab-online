<?php

namespace App\Filament\Resources\PengajuanMarcommKebutuhanAmplopResource\Pages;

use App\Filament\Resources\PengajuanMarcommKebutuhanAmplopResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengajuanMarcommKebutuhanAmplop extends EditRecord
{
    protected static string $resource = PengajuanMarcommKebutuhanAmplopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
