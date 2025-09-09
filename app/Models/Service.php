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
    ];

    // Relationship dengan user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relationship dengan logs (termasuk yang soft deleted)
    public function stagingLogs(): HasMany
    {
        return $this->hasMany(ServiceStagingLog::class);
    }

    // Relationship dengan logs (hanya yang tidak soft deleted)
    public function activeStagingLogs(): HasMany
    {
        return $this->hasMany(ServiceStagingLog::class)->whereNull('deleted_at');
    }

    // Relationship dengan logs termasuk yang soft deleted
    public function stagingLogsWithTrashed(): HasMany
    {
        return $this->hasMany(ServiceStagingLog::class)->withTrashed();
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
}
