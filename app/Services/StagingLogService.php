<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ServiceStagingLog;
use Illuminate\Support\Facades\Auth;

class StagingLogService
{
    public static function logStagingChange(Service $service, ?string $oldStaging, string $newStaging, ?string $keterangan = null): void
    {
        $user = Auth::user();

        ServiceStagingLog::create([
            'service_id' => $service->id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->getRoleNames()->first() ?? 'Unknown',
            'old_staging' => $oldStaging,
            'new_staging' => $newStaging,
            'keterangan' => $keterangan
        ]);
    }
}
