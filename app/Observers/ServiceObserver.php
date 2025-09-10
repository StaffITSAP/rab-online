<?php

namespace App\Observers;

use App\Models\Service;
use App\Services\ServiceLogService;
use App\Enums\StagingEnum;

class ServiceObserver
{
    public function created(Service $service): void
    {
        ServiceLogService::logCreation($service);
    }

    public function updated(Service $service): void
    {
        $changes = $service->getChanges();
        $original = $service->getOriginal();

        foreach ($changes as $field => $newValue) {
            if (in_array($field, ['updated_at', 'created_at'])) continue;

            $oldValue = $original[$field] ?? null;

            if ($field === 'staging') {
                ServiceLogService::logStagingChange($service, $oldValue, $newValue);
            } else {
                ServiceLogService::logChange(
                    $service,
                    $field,
                    $oldValue,
                    $newValue
                );
            }
        }
    }

    public function deleted(Service $service): void
    {
        ServiceLogService::logDeletion($service);
    }

    public function restored(Service $service): void
    {
        ServiceLogService::logChange(
            $service,
            'deleted_at',
            $service->deleted_at,
            null,
            'restore',
            'Service dipulihkan'
        );
    }

    public function forceDeleted(Service $service): void
    {
        ServiceLogService::logChange(
            $service,
            'all',
            $service->toArray(),
            null,
            'force_delete',
            'Service dihapus permanen'
        );
    }
}
