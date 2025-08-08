<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengajuanMarcommPromosi extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pengajuan_id',
        'deskripsi',
        'qty',
        'harga_satuan',
        'subtotal',
        'keterangan',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }
}
