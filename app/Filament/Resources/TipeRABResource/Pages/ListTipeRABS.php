<?php

namespace App\Filament\Resources\TipeRABResource\Pages;

use App\Filament\Resources\TipeRABResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTipeRABS extends ListRecords
{
    protected static string $resource = TipeRABResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
