<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class PengajuanDinasActivity extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pengajuan_dinas_activities';


    protected $fillable = [
        'pengajuan_id',
        'no_activity',
        'nama_dinas',
        'keterangan',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }
}
