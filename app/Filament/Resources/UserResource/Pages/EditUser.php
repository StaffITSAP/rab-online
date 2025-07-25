<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $userStatusData = $data['userStatus'] ?? [];
        unset($data['userStatus']);

        // Update atau buat userStatus
        if ($this->record->userStatus) {
            $this->record->userStatus->update($userStatusData);
        } else {
            $this->record->userStatus()->create($userStatusData);
        }

        return $data;
    }
}
