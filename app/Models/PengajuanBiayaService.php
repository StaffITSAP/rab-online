<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanBiayaService extends Model
{
    use SoftDeletes;

    protected $table = 'pengajuan_biaya_services';

    protected $fillable = [
        'pengajuan_id',
        'service_id',
        'service_item_id',
        'deskripsi',
        'jumlah',
        'harga_satuan',
        'subtotal',
        'pph_persen',
        'pph_nominal',
        'dpp_jual',
        'total'
    ];

    protected static function booted()
    {
        // Event ketika model disimpan (create atau update)
        static::saved(function ($model) {
            $model->updatePengajuanTotalBiaya();
        });

        // Event ketika model dihapus
        static::deleted(function ($model) {
            $model->updatePengajuanTotalBiaya();
        });
    }

    /**
     * Update total_biaya di model Pengajuan
     */
    public function updatePengajuanTotalBiaya()
    {
        if ($this->pengajuan) {
            $totalBiaya = $this->pengajuan->pengajuan_biaya_services()->sum('total');
            $this->pengajuan->update(['total_biaya' => $totalBiaya]);
        }
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function item()
    {
        return $this->belongsTo(ServiceItem::class, 'service_item_id');
    }

    public function hitungOtomatis()
    {
        $this->subtotal = $this->jumlah * $this->harga_satuan;

        if ($this->pph_persen) {
            $this->pph_nominal = round($this->subtotal * ($this->pph_persen / 100));
            $this->dpp_jual = $this->subtotal - $this->pph_nominal;
            $this->total = $this->subtotal;
        } else {
            $this->pph_nominal = 0;
            $this->dpp_jual = $this->subtotal;
            $this->total = $this->subtotal;
        }
    }

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id');
    }

    public function service_item(): BelongsTo
    {
        return $this->belongsTo(ServiceItem::class, 'service_item_id');
    }
}
