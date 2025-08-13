<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengajuanMarcommKegiatan extends Model
{
    use SoftDeletes;
    protected $table = 'pengajuan_marcomm_kegiatans';

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

    public function marcommKegiatanPusats()
    {
        return $this->hasMany(LampiranMarcommKegiatanPusat::class, 'pengajuan_id');
    }
    public function marcommKegiatanCabangs()
    {
        return $this->hasMany(LampiranMarcommKegiatanCabang::class, 'pengajuan_id');
    }
}
