<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LampiranAsset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pengajuan_id',
        'file_path',
        'original_name',
    ];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(Pengajuan::class);
    }
}
