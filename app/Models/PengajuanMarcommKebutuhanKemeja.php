<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengajuanMarcommKebutuhanKemeja extends Model
{
    use SoftDeletes;

    protected $table = 'pengajuan_marcomm_kebutuhan_kemejas';

    protected $fillable = [
        'pengajuan_id',
        'nama',
        'ukuran',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }
}
