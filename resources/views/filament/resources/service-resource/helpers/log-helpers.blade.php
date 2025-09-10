@php
/**
 * =========================
 *  LOG HELPERS – TANGGUH
 * =========================
 */

// 1) Label field
if (! function_exists('getFieldLabel')) {
    function getFieldLabel(string $field): string {
        $map = [
            'id_paket'           => 'ID Paket',
            'staging'            => 'Staging',
            'nama_dinas'         => 'Nama Dinas',
            'kontak'             => 'Kontak',
            'no_telepon'         => 'No. Telepon',
            'kerusakan'          => 'Kerusakan',
            'nama_barang'        => 'Nama Barang',
            'noserial'           => 'No. Serial',
            'masih_garansi'      => 'Status Garansi',
            'nomer_so'           => 'No. SO',
            'keterangan_staging' => 'Keterangan Staging',
            'all'                => 'Semua Field',
        ];
        return $map[$field] ?? ucwords(str_replace('_', ' ', $field));
    }
}

// 2) Normalisasi payload ke array
if (! function_exists('normalizeLogPayload')) {
    /**
     * Kembalikan array untuk berbagai kemungkinan input:
     * - array langsung
     * - object stdClass
     * - string JSON (termasuk double-encoded)
     * - string object-like (key tanpa quote)
     */
    function normalizeLogPayload($value): ?array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_object($value)) {
            return json_decode(json_encode($value), true);
        }

        if (! is_string($value)) {
            return null;
        }

        $json = $value;

        // Coba decode sampai 3 kali (mengatasi double-encoded / triple-encoded)
        for ($i = 0; $i < 3; $i++) {
            $decoded = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                if (is_array($decoded)) {
                    return $decoded;
                }
                if (is_string($decoded)) {
                    // kasus: hasil decode masih string JSON
                    $json = $decoded;
                    continue;
                }
            }

            // Perbaiki key tanpa quote: {id_paket:"Baru"} -> {"id_paket":"Baru"}
            $fixed = preg_replace('/([{\s,])([A-Za-z0-9_]+)\s*:/', '$1"$2":', $json);
            if ($fixed !== null && $fixed !== $json) {
                $json = $fixed;
                // lanjut loop untuk coba decode lagi
                continue;
            }

            break;
        }

        return null;
    }
}

// 3) Format nilai single field
if (! function_exists('formatSingleLogValue')) {
    function formatSingleLogValue(string $field, $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        if ($field === 'staging') {
            return \App\Enums\StagingEnum::tryFrom((string) $value)?->label() ?? (string) $value;
        }

        if ($field === 'masih_garansi') {
            if (is_bool($value)) {
                return $value ? 'Ya' : 'Tidak';
            }
            $v = strtoupper((string) $value);
            return in_array($v, ['Y','1','TRUE','T'], true) ? 'Tidak' /* fallback? */ : (in_array($v, ['N','0','FALSE','F'], true) ? 'Tidak' : ((string) $value));
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }
}

// 4) Buat multi-baris "Label : Nilai" dari array payload
if (! function_exists('linesFromPayload')) {
    function linesFromPayload(array $data): string
    {
        $ordered = [
            'id_paket','nama_dinas','kontak','no_telepon','kerusakan',
            'nama_barang','noserial','masih_garansi','nomer_so','staging','keterangan_staging',
        ];
        $ignored = ['id','user_id','created_at','updated_at'];

        $lines = [];

        foreach ($ordered as $k) {
            if (array_key_exists($k, $data)) {
                $lines[] = getFieldLabel($k) . ' : ' . formatSingleLogValue($k, $data[$k]);
            }
        }

        foreach ($data as $k => $v) {
            if (in_array($k, $ordered, true) || in_array($k, $ignored, true)) {
                continue;
            }
            $lines[] = getFieldLabel($k) . ' : ' . formatSingleLogValue($k, $v);
        }

        return implode("\n", $lines);
    }
}

// 5) Entry point: dipakai di Blade
if (! function_exists('formatLogValue')) {
    /**
     * @param string $field       Nama field (atau 'all')
     * @param mixed  $value       Nilai old/new (bisa JSON/string/array)
     * @param string|null $type   change_type ('create','update',...)
     */
    function formatLogValue(string $field, $value, ?string $type = null): string
    {
        $payload = normalizeLogPayload($value);

        // Create / all => multi-baris
        if (($field === 'all' || $type === 'create') && is_array($payload)) {
            return linesFromPayload($payload);
        }

        // Single field / gagal decode => format biasa
        return formatSingleLogValue($field, $payload ?? $value);
    }
}
@endphp
