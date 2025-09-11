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
            'service_id'    => $service->id,
            'user_id'       => $user?->id,
            'user_name'     => $user?->name ?? 'System',
            'user_role'     => method_exists($user, 'getRoleNames') ? $user->getRoleNames()->first() : ($user?->username ?? 'Unknown'),
            'field_changed' => $fieldChanged,
            'old_value'     => self::formatValue($fieldChanged, $oldValue),
            'new_value'     => self::formatValue($fieldChanged, $newValue),
            'change_type'   => $changeType,
            'keterangan'    => $keterangan,
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
            'service_id'    => $service->id,
            'user_id'       => $user?->id,
            'user_name'     => $user?->name ?? 'System',
            'user_role'     => method_exists($user, 'getRoleNames') ? $user->getRoleNames()->first() : ($user?->username ?? 'Unknown'),
            'field_changed' => 'all',
            'old_value'     => null,
            'new_value'     => json_encode($service->toArray(), JSON_PRETTY_PRINT),
            'change_type'   => 'create',
            'keterangan'    => 'Service berhasil dibuat',
        ]);
    }

    public static function logDeletion(Service $service, bool $forceDelete = false): void
    {
        $user = Auth::user();

        ServiceLog::create([
            'service_id'    => $service->id,
            'user_id'       => $user?->id,
            'user_name'     => $user?->name ?? 'System',
            'user_role'     => method_exists($user, 'getRoleNames') ? $user->getRoleNames()->first() : ($user?->username ?? 'Unknown'),
            'field_changed' => 'all',
            'old_value'     => json_encode($service->toArray(), JSON_PRETTY_PRINT),
            'new_value'     => null,
            'change_type'   => $forceDelete ? 'force_delete' : 'delete',
            'keterangan'    => $forceDelete ? 'Service dihapus permanen' : 'Service dihapus',
        ]);
    }

    public static function logIdPaketChange(Service $service, ?string $oldIdPaket, string $newIdPaket, ?string $keterangan = null): void
    {
        self::logChange($service, 'id_paket', $oldIdPaket, $newIdPaket, 'update', $keterangan);
    }

    private static function formatValue(string $field, $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT);
        }

        if (is_string($value) && self::isJson($value)) {
            $decoded = json_decode($value, true);
            return json_encode($decoded, JSON_PRETTY_PRINT);
        }

        if ($field === 'staging') {
            $enumValue = StagingEnum::tryFrom($value);
            return $enumValue ? $enumValue->label() : $value;
        }

        if ($field === 'masih_garansi') {
            return $value === 'Y' ? 'Ya' : ($value === 'T' ? 'Tidak' : (string) $value);
        }

        return (string) $value;
    }

    private static function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
