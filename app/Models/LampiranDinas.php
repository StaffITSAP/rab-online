<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LampiranDinas extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lampiran_dinas';

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
