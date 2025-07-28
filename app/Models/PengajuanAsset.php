<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class PengajuanAsset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pengajuan_id',
        'nama_barang',
        'tipe_barang',
        'jumlah',
        'keperluan',
        'harga_unit',
        'subtotal',
        'keterangan',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }
}
