<?php

namespace App\Observers;

use App\Models\Service;
use App\Services\ServiceLogService;

class ServiceObserver
{
    public function created(Service $service): void
    {
        ServiceLogService::logCreation($service);
    }

    public function deleting(Service $service): void
    {
        if ($service->isForceDeleting()) {
            // Hapus semua log permanen
            $service->serviceLogs()->forceDelete();
            // Catat log sebelum service hilang
            ServiceLogService::logDeletion($service, true);
        } else {
            // Soft delete: tandai log ikut soft delete
            $service->serviceLogs()->delete();
            // Catat log soft delete
            ServiceLogService::logDeletion($service, false);
        }
    }

    public function restoring(Service $service): void
    {
        // Log ikut dipulihkan
        $service->serviceLogs()->withTrashed()->restore();

        ServiceLogService::logChange(
            $service,
            'deleted_at',
            now(),
            null,
            'restore',
            'Service dipulihkan'
        );
    }
}
