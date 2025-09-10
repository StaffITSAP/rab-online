<?php

namespace App\Models;

use App\Enums\StagingEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_id',
        'user_id',
        'user_name',
        'user_role',
        'field_changed',
        'old_value',
        'new_value',
        'change_type',
        'keterangan'
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessor untuk label field
    public function getFieldLabelAttribute(): string
    {
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
        ];

        return $fieldLabels[$this->field_changed] ?? $this->field_changed;
    }

    // Accessor untuk format nilai lama
    public function getOldValueFormattedAttribute(): string
    {
        return $this->formatValue($this->field_changed, $this->old_value);
    }

    // Accessor untuk format nilai baru
    public function getNewValueFormattedAttribute(): string
    {
        return $this->formatValue($this->field_changed, $this->new_value);
    }

    // Helper method untuk format nilai
    private function formatValue(string $field, $value): string
    {
        if (is_null($value)) {
            return '-';
        }

        // Jika value adalah array
        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT);
        }

        // Jika value adalah string JSON
        if (is_string($value) && $this->isJson($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return json_encode($decoded, JSON_PRETTY_PRINT);
            }
        }

        // Format khusus untuk enum staging
        if ($field === 'staging') {
            $enumValue = StagingEnum::tryFrom($value);
            return $enumValue ? $enumValue->label() : $value;
        }

        // Format khusus untuk boolean
        if ($field === 'masih_garansi') {
            return $value === 'Y' ? 'Ya' : 'Tidak';
        }

        return (string) $value;
    }

    // Check if string is JSON
    private function isJson($string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    // Di dalam model ServiceLog
    public function getOldStagingLabelAttribute(): string
    {
        if ($this->field_changed !== 'staging') return '-';

        return $this->old_value ? StagingEnum::tryFrom($this->old_value)?->label() ?? $this->old_value : '-';
    }

    public function getNewStagingLabelAttribute(): string
    {
        if ($this->field_changed !== 'staging') return '-';

        return StagingEnum::tryFrom($this->new_value)?->label() ?? $this->new_value;
    }
}
