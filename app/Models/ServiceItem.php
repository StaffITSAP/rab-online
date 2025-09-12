<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceItem extends Model
{
    use SoftDeletes;

    protected $table = 'service_items';

    protected $fillable = [
        'service_id',
        'kerusakan',
        'nama_barang',
        'noserial',
        'masih_garansi',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function pengajuanBiaya()
    {
        return $this->hasMany(PengajuanBiayaService::class, 'service_item_id');
    }
}
