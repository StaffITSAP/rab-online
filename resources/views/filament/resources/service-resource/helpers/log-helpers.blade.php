@php
function getFieldLabel($field) {
$fieldLabels = [
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
return $fieldLabels[$field] ?? $field;
}

function formatLogValue($field, $value) {
if (is_null($value)) {
return '-';
}

if (is_array($value)) {
return json_encode($value, JSON_PRETTY_PRINT);
}

if (is_string($value) && isJson($value)) {
$decoded = json_decode($value, true);
if (json_last_error() === JSON_ERROR_NONE) {
return json_encode($decoded, JSON_PRETTY_PRINT);
}
}

if ($field === 'staging') {
$enumValue = \App\Enums\StagingEnum::tryFrom($value);
return $enumValue ? $enumValue->label() : $value;
}

if ($field === 'masih_garansi') {
return $value === 'Y' ? 'Ya' : 'Tidak';
}

return $value;
}

function isJson($string) {
json_decode($string);
return json_last_error() === JSON_ERROR_NONE;
}
@endphp