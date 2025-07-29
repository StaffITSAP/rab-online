<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Pengajuan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'no_rab',
        'tipe_rab_id',
        'status',
        'total_biaya',
        'tgl_realisasi',
        'tgl_pulang',
        'jam',
        'deletion_reason',
        'jml_personil'
    ];

    protected $dates = ['tgl_realisasi', 'tgl_pulang'];

    protected static function booted()
    {
        static::creating(function ($pengajuan) {
            $pengajuan->no_rab = self::generateNoRAB($pengajuan->tipe_rab_id);
        });

        static::retrieved(function ($pengajuan) {
            if ($pengajuan->status === 'menunggu' && now()->diffInDays($pengajuan->created_at) > 2) {
                $pengajuan->status = 'expired';
                $pengajuan->saveQuietly();
            }
        });

        static::saving(function ($pengajuan) {
            if ($pengajuan->relationLoaded('pengajuan_assets')) {
                $pengajuan->total_biaya = $pengajuan->pengajuan_assets->sum('subtotal');
            }
        });
    }

    public static function generateNoRAB(int $tipeRABId): string
    {
        $today = now();
        $dateStr = $today->format('ymd'); // contoh: 250726
        $year = $today->year;

        // Ambil kode dari tabel tipe_rabs
        $tipeRAB = \App\Models\TipeRAB::find($tipeRABId);
        $kodeTipe = $tipeRAB?->kode ?? 'XX'; // fallback 'XX' jika tidak ditemukan

        // Hitung jumlah pengajuan dengan tipe sama dan tahun yang sama
        $count = self::where('tipe_rab_id', $tipeRABId)
            ->whereYear('created_at', $year)
            ->count();

        $urut = str_pad(($count + 1), 5, '0', STR_PAD_LEFT);

        return "RAB/{$kodeTipe}/{$dateStr}/{$urut}";
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tipeRAB()
    {
        return $this->belongsTo(TipeRAB::class, 'tipe_rab_id');
    }
    public function assets()
    {
        return $this->hasMany(PengajuanAsset::class);
    }
    public function pengajuan_assets()
    {
        return $this->hasMany(PengajuanAsset::class);
    }
    public function statuses()
    {
        return $this->hasMany(PengajuanStatus::class);
    }
    public function barangs()
    {
        return $this->hasMany(PengajuanAsset::class); // ganti sesuai modelmu
    }
    public function dinas()
    {
        return $this->hasMany(PengajuanDinas::class);
    }
    public function pengajuan_dinas()
    {
        return $this->hasMany(PengajuanDinas::class);
    }
}
