<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LampiranMarcommKegiatanCabang extends Model
{
    use SoftDeletes;

    protected $table = 'lampiran_marcomm_kegiatans_cabang';

    protected $fillable = [
        'pengajuan_id',
        'cabang',
        'nama',
        'gender',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id');
    }
}
