<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Agar field userStatus (nested relasi) tetap terisi saat edit.
     * Akan mengisi form dengan data userStatus jika ada di DB.
     */
    protected function getFormModelData(): array
    {
        $data = parent::getFormModelData();

        // Inject data userStatus agar nested field tidak kosong saat edit.
        // Jika relasi userStatus ada di database, isi, jika tidak, kosongkan array.
        $data['userStatus'] = $this->record->userStatus ? $this->record->userStatus->toArray() : [];

        return $data;
    }

    /**
     * Saat submit form, update atau create userStatus
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $userStatusData = $data['userStatus'] ?? [];
        unset($data['userStatus']); // Remove userStatus from user fields

        // Update atau buat relasi userStatus
        if ($this->record->userStatus) {
            $this->record->userStatus->update($userStatusData);
        } else {
            $this->record->userStatus()->create($userStatusData);
        }

        return $data;
    }
}
