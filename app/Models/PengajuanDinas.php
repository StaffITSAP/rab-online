<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengajuanDinas extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'pengajuan_id',
        'deskripsi',
        'keterangan',
        'pic',
        'jml_hari',
        'harga_satuan',
        'subtotal',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }
}
