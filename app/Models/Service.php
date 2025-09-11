<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\StagingEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'id_paket',
        'nama_dinas',
        'kontak',
        'no_telepon',
        'kerusakan',
        'nama_barang',
        'noserial',
        'masih_garansi',
        'nomer_so',
        'staging',
        'keterangan_staging'
    ];

    protected $casts = [
        'staging' => StagingEnum::class,
        // HAPUS casting boolean untuk masih_garansi
        // 'masih_garansi' => 'boolean',
    ];

    public static function generateNomorSO(): string
    {
        $prefix = 'SRV/' . now()->format('ymd');

        // Ambil record terakhir (termasuk soft deleted) yang punya nomor_so
        $lastRecord = self::withTrashed()
            ->where('nomer_so', 'like', $prefix . '/%')
            ->orderByDesc('id')
            ->first();

        if ($lastRecord) {
            $lastNumber = (int) substr($lastRecord->nomer_so, -5); // ambil 5 digit terakhir
            $nextNumber = $lastNumber + 1;

            if ($nextNumber > 99999) {
                $nextNumber = 1; // reset ke 00001
            }
        } else {
            $nextNumber = 1;
        }

        return $prefix . '/' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
    // Relationship dengan user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relationship dengan semua logs service
    public function serviceLogs(): HasMany
    {
        return $this->hasMany(ServiceLog::class)->orderBy('created_at', 'desc');
    }

    // Relationship dengan logs staging saja
    public function stagingLogs(): HasMany
    {
        return $this->hasMany(ServiceLog::class)
            ->where('field_changed', 'staging')
            ->orderBy('created_at', 'desc');
    }

    // Accessor untuk mendapatkan nilai string dari enum
    public function getStagingValueAttribute(): string
    {
        return $this->staging->value;
    }

    // Accessor untuk mendapatkan label dari enum
    public function getStagingLabelAttribute(): string
    {
        return $this->staging->label();
    }

    // Accessor untuk mendapatkan label garansi
    public function getMasihGaransiLabelAttribute(): string
    {
        return $this->masih_garansi === 'Y' ? 'Ya' : 'Tidak';
    }

    // Scope untuk filtering berdasarkan staging
    public function scopeWhereStaging($query, $staging)
    {
        if ($staging instanceof StagingEnum) {
            return $query->where('staging', $staging->value);
        }

        if (is_string($staging) && StagingEnum::tryFrom($staging)) {
            return $query->where('staging', $staging);
        }

        return $query;
    }

    // Scope untuk filtering berdasarkan garansi
    public function scopeWhereMasihGaransi($query, $value)
    {
        if ($value === true || $value === 'Y') {
            return $query->where('masih_garansi', 'Y');
        }

        if ($value === false || $value === 'T') {
            return $query->where('masih_garansi', 'T');
        }

        return $query;
    }

    public function items()
    {
        return $this->hasMany(ServiceItem::class, 'service_id');
    }
}
