<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use App\Services\ServiceLogService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    // Simpan data original sebelum diubah
    protected array $originalData = [];

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }

    protected function beforeSave(): void
    {
        // Simpan data original sebelum perubahan
        $this->originalData = $this->record->getOriginal();
    }

    protected function afterSave(): void
    {
        // Dapatkan data yang berubah
        $changedData = $this->record->getChanges();

        // Simpan log untuk setiap field yang berubah
        foreach ($changedData as $field => $newValue) {
            if (in_array($field, ['updated_at', 'created_at'])) {
                continue; // Skip timestamp fields
            }

            $oldValue = $this->originalData[$field] ?? null;

            // Handle khusus untuk staging
            if ($field === 'staging') {
                ServiceLogService::logStagingChange($this->record, $oldValue, $newValue, 'Diubah melalui form edit');
            } else {
                ServiceLogService::logChange(
                    $this->record,
                    $field,
                    $oldValue,
                    $newValue,
                    'update',
                    'Diubah melalui form edit'
                );
            }
        }
    }

    protected function afterDelete(): void
    {
        // Log penghapusan service
        ServiceLogService::logDeletion($this->record);
    }
}