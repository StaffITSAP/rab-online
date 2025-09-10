<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ServiceLog;
use App\Enums\StagingEnum;
use Illuminate\Support\Facades\Auth;

class ServiceLogService
{
    public static function logChange(
        Service $service,
        string $fieldChanged,
        $oldValue,
        $newValue,
        string $changeType = 'update',
        ?string $keterangan = null
    ): void {
        $user = Auth::user();

        ServiceLog::create([
            'service_id' => $service->id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->getRoleNames()->first() ?? 'Unknown',
            'field_changed' => $fieldChanged,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'change_type' => $changeType,
            'keterangan' => $keterangan
        ]);
    }

    public static function logStagingChange(Service $service, $oldStaging, $newStaging, ?string $keterangan = null): void
    {
        self::logChange($service, 'staging', $oldStaging, $newStaging, 'staging_change', $keterangan);
    }

    public static function logCreation(Service $service): void
    {
        $user = Auth::user();

        ServiceLog::create([
            'service_id' => $service->id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->getRoleNames()->first() ?? 'Unknown',
            'field_changed' => 'all',
            'old_value' => null,
            'new_value' => json_encode($service->toArray()), // Convert array to JSON string
            'change_type' => 'create',
            'keterangan' => 'Service berhasil dibuat'
        ]);
    }

    public static function logDeletion(Service $service): void
    {
        $user = Auth::user();

        ServiceLog::create([
            'service_id' => $service->id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->getRoleNames()->first() ?? 'Unknown',
            'field_changed' => 'all',
            'old_value' => $service->toArray(),
            'new_value' => null,
            'change_type' => 'delete',
            'keterangan' => 'Service dihapus'
        ]);
    }

    public static function logIdPaketChange(Service $service, ?string $oldIdPaket, string $newIdPaket, ?string $keterangan = null): void
    {
        self::logChange($service, 'id_paket', $oldIdPaket, $newIdPaket, 'update', $keterangan);
    }
    protected $casts = [
        'staging' => StagingEnum::class,
        'masih_garansi' => 'boolean', // Convert 'Y'/'T' to true/false
    ];
    // Di ServiceLogService, pastikan formatValue menangani masih_garansi dengan benar
    private function formatValue(string $field, $value): string
    {
        if (is_null($value)) {
            return '-';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT);
        }

        if (is_string($value) && $this->isJson($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return json_encode($decoded, JSON_PRETTY_PRINT);
            }
        }

        if ($field === 'staging') {
            $enumValue = StagingEnum::tryFrom($value);
            return $enumValue ? $enumValue->label() : $value;
        }

        if ($field === 'masih_garansi') {
            return $value === 'Y' ? 'Ya' : 'Tidak';
        }

        return (string) $value;
    }
}
