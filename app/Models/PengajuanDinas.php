<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\PengajuanDinasLampiran;

class PengajuanDinas extends Model
{
    use SoftDeletes;

    protected $table = 'pengajuan_dinas';


    protected $fillable = [
        'pengajuan_id',
        'deskripsi',
        'keterangan',
        'pic',
        'jml_hari',
        'harga_satuan',
        'subtotal',
    ];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id', 'id');
    }
}
