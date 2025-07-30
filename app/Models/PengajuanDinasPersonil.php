<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PengajuanDinasPersonil extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pengajuan_id',
        'nama_personil',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }
}
