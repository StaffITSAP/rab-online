<?php

namespace App\Filament\Resources\TipeRABResource\Pages;

use App\Filament\Resources\TipeRABResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTipeRAB extends EditRecord
{
    protected static string $resource = TipeRABResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
