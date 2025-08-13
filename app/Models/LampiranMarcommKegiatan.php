<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LampiranMarcommKegiatan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pengajuan_id',
        'file_path',
        'original_name',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }
}
