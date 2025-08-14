<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LampiranMarcommKegiatanPusat extends Model
{
    use SoftDeletes;

    protected $table = 'lampiran_marcomm_kegiatans_pusat';

    protected $fillable = [
        'pengajuan_id',
        'nama',
        'gender',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id');
    }
}
