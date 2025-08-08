<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengajuanMarcommKebutuhan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pengajuan_id',
        'deskripsi',
        'qty',
        'harga_satuan',
        'subtotal',
        'tipe',
        'total_amplop',
        'kebutuhan_amplop',
        'kebutuhan_kartu',
        'kebutuhan_kemeja',
    ];
    protected $casts = [
        'kebutuhan_amplop' => 'boolean',
        'kebutuhan_kartu' => 'boolean',
        'kebutuhan_kemeja' => 'boolean'
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }
    public function kebutuhan_amplop()
    {
        return $this->hasMany(PengajuanMarcommKebutuhanAmplop::class);
    }
}
