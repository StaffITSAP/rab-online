<?php

namespace App\Observers;

use App\Models\Service;
use App\Models\ServiceStagingLog;

class ServiceObserver
{
    /**
     * Handle the Service "deleted" event.
     */
    public function deleted(Service $service): void
    {
        // Soft delete semua staging logs terkait
        if ($service->isForceDeleting()) {
            // Jika force delete, hapus permanen
            ServiceStagingLog::where('service_id', $service->id)->forceDelete();
        } else {
            // Jika soft delete, soft delete juga logs-nya
            ServiceStagingLog::where('service_id', $service->id)->delete();
        }
    }

    /**
     * Handle the Service "restored" event.
     */
    public function restored(Service $service): void
    {
        // Restore semua staging logs terkait
        ServiceStagingLog::where('service_id', $service->id)->restore();
    }

    /**
     * Handle the Service "forceDeleted" event.
     */
    public function forceDeleted(Service $service): void
    {
        // Force delete semua staging logs terkait
        ServiceStagingLog::where('service_id', $service->id)->forceDelete();
    }
}
