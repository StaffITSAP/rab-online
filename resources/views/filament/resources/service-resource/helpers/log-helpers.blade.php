@php
use App\Enums\StagingEnum;

/* ======== Label field (sinkron dengan RelationManager::getFieldLabel) ======== */
if (! function_exists('getFieldLabel')) {
function getFieldLabel(string $field): string {
$labels = [
'id_paket' => 'ID Paket',
'staging' => 'Staging',
'nama_dinas' => 'Nama Dinas',
'kontak' => 'Kontak',
'no_telepon' => 'No. Telepon',
'kerusakan' => 'Kerusakan',
'nama_barang' => 'Nama Barang',
'noserial' => 'No. Serial',
'masih_garansi' => 'Status Garansi',
'nomer_so' => 'No. SO',
'keterangan_staging' => 'Keterangan Staging',
'all' => 'Semua Field',
];
return $labels[$field] ?? $field;
}
}

/* ======== Normalisasi + format untuk single value (sinkron dengan formatValue) ======== */
if (! function_exists('formatSingleValue')) {
function formatSingleValue(string $field, $value): string {
if ($value === null || $value === '') return '-';

if ($field === 'staging') {
return StagingEnum::tryFrom((string) $value)?->label() ?? (string) $value;
}

if ($field === 'masih_garansi') {
if (is_bool($value)) return $value ? 'Ya' : 'Tidak';
$v = strtoupper((string) $value);
return in_array($v, ['Y','1','TRUE'], true) ? 'Ya' : 'Tidak';
}

if (is_array($value)) {
return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

/* jika string JSON valid → pretty */
if (is_string($value)) {
$tmp = json_decode($value, true);
if (json_last_error() === JSON_ERROR_NONE && is_array($tmp)) {
return json_encode($tmp, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
}

return (string) $value;
}
}

/* ======== Create/All: buat “Label : Nilai” per baris (sinkron dengan prettyLinesFromArray/lineFor) ======== */
if (! function_exists('linesFromArray')) {
function linesFromArray(array $data): string {
$ordered = [
'id_paket','nama_dinas','kontak','no_telepon','kerusakan',
'nama_barang','noserial','masih_garansi','nomer_so','staging','keterangan_staging',
];
$ignored = ['id','user_id','created_at','updated_at'];

$lines = [];
foreach ($ordered as $k) {
if (array_key_exists($k, $data)) {
$lines[] = getFieldLabel($k) . ' : ' . formatSingleValue($k, $data[$k]);
}
}
foreach ($data as $k => $v) {
if (in_array($k, $ordered, true) || in_array($k, $ignored, true)) continue;
$lines[] = getFieldLabel($k) . ' : ' . formatSingleValue($k, $v);
}
return implode("\n", $lines);
}
}

/* ======== Entry point yang dipakai di Blade ======== */
if (! function_exists('formatLogValue')) {
/**
* @param string $field nama field (atau 'all')
* @param mixed $value old/new value (bisa array, JSON string, scalar)
* @param ?string $type change_type
*/
function formatLogValue(string $field, $value, ?string $type = null): string {
// coba decode jika string JSON
$decoded = null;
if (is_string($value)) {
$tmp = json_decode($value, true);
if (json_last_error() === JSON_ERROR_NONE && is_array($tmp)) {
$decoded = $tmp;
}
} elseif (is_array($value)) {
$decoded = $value;
}

$isCreateLike = ($field === 'all') || ($type === 'create');
if ($isCreateLike && is_array($decoded)) {
return linesFromArray($decoded);
}

return formatSingleValue($field, $value);
}
}
@endphp