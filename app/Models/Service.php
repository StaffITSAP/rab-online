<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\StagingEnum;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Service extends Model
{
    use HasFactory;

    protected $fillable = [
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
        'keterangan_staging',
        'user_id'
    ];

    protected $casts = [
        'staging' => StagingEnum::class,
    ];
    // Relationship dengan logs
    public function stagingLogs(): HasMany
    {
        return $this->hasMany(ServiceStagingLog::class);
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
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
