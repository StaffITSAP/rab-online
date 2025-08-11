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
    /**
     * Set toggle kebutuhan amplop ke semua baris milik pengajuan
     */
    public static function writeAmplopToggle(int $pengajuanId, bool $on): void
    {
        static::where('pengajuan_id', $pengajuanId)->update(['kebutuhan_amplop' => $on]);
    }

    public static function writeKartuToggle(int $pengajuanId, bool $on): void
    {
        static::where('pengajuan_id', $pengajuanId)->update(['kebutuhan_kartu' => $on]);
    }


    /**
     * Hitung total amplop dari tabel detail dan simpan hanya di 1 baris (baris pertama).
     * Baris lainnya dikosongkan (NULL).
     */
    public static function syncTotalAmplop(int $pengajuanId): void
    {
        $sum = PengajuanMarcommKebutuhanAmplop::where('pengajuan_id', $pengajuanId)->sum('jumlah');

        $rows = static::where('pengajuan_id', $pengajuanId)->orderBy('id')->get(['id']);
        if ($rows->isEmpty()) {
            return;
        }

        // Kosongkan semua dulu
        static::whereIn('id', $rows->pluck('id'))->update(['total_amplop' => null]);

        // Tulis hanya di baris pertama
        static::where('id', $rows->first()->id)->update(['total_amplop' => $sum]);
    }
    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }
    public function kebutuhan_amplop()
    {
        return $this->hasMany(PengajuanMarcommKebutuhanAmplop::class);
    }
}
