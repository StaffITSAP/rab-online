<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengajuanMarcommKebutuhanKartu extends Model
{
    use SoftDeletes;

    protected $table = 'pengajuan_marcomm_kebutuhan_kartus';

    protected $fillable = [
        'pengajuan_id',
        'kartu_nama',
        'id_card',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }
}
