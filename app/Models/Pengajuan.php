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
        'jml_personil',
        'menggunakan_teknisi',
        'use_pengiriman',
        'use_car',
        'asset_teknisi',
    ];

    protected $casts = [
        'menggunakan_teknisi' => 'boolean',
        'use_pengiriman' => 'boolean',
        'use_car' => 'boolean',
        'asset_teknisi' => 'boolean',
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
            if ($pengajuan->tipe_rab_id == 1 && $pengajuan->relationLoaded('pengajuan_assets')) {
                $pengajuan->total_biaya = $pengajuan->pengajuan_assets->sum('subtotal');
            } elseif ($pengajuan->tipe_rab_id == 2 && $pengajuan->relationLoaded('pengajuan_dinas')) {
                $pengajuan->total_biaya = $pengajuan->pengajuan_dinas->sum('subtotal');
            }
        });

        static::deleting(function ($pengajuan) {
            // Soft delete check
            if (method_exists($pengajuan, 'isForceDeleting') && !$pengajuan->isForceDeleting()) {
                // Soft delete relasi-relasi terkait
                $pengajuan->pengajuan_assets()->get()->each(function ($asset) {
                    $asset->delete();
                });

                $pengajuan->pengajuan_dinas()->get()->each(function ($dinas) {
                    $dinas->delete();
                });

                $pengajuan->dinasActivities()->get()->each(function ($activity) {
                    $activity->delete();
                });

                $pengajuan->dinasPersonils()->get()->each(function ($personil) {
                    $personil->delete();
                });

                $pengajuan->lampiran()->get()->each(function ($lampiran) {
                    $lampiran->delete();
                });

                $pengajuan->lampiranAssets()->get()->each(function ($lampiranAsset) {
                    $lampiranAsset->delete();
                });

                $pengajuan->lampiranDinas()->get()->each(function ($lampiranDinas) {
                    $lampiranDinas->delete();
                });
            }
        });

        // Untuk restore otomatis jika ingin (opsional)
        static::restoring(function ($pengajuan) {
            $pengajuan->pengajuan_assets()->withTrashed()->get()->each->restore();
            $pengajuan->pengajuan_dinas()->withTrashed()->get()->each->restore();
            $pengajuan->dinasActivities()->withTrashed()->get()->each->restore();
            $pengajuan->dinasPersonils()->withTrashed()->get()->each->restore();
            $pengajuan->lampiran()->withTrashed()->get()->each->restore();
            $pengajuan->lampiranAssets()->withTrashed()->get()->each->restore();
            $pengajuan->lampiranDinas()->withTrashed()->get()->each->restore();
        });
    }

    public static function generateNoRAB(int $tipeRABId): string
    {
        $today = now();
        $dateStr = $today->format('ymd'); // contoh: 250802
        $year = $today->year;

        // Ambil kode dari tabel tipe_rabs
        $tipeRAB = \App\Models\TipeRab::find($tipeRABId);
        $kodeTipe = $tipeRAB?->kode ?? 'XX';

        // Pattern prefix (tanpa urut)
        $prefix = "RAB/{$kodeTipe}/{$dateStr}/";

        // Cari nomor urut terbesar untuk tipe RAB ini saja (semua tahun, semua tanggal, termasuk soft delete)
        $last = self::withTrashed()
            ->where('tipe_rab_id', $tipeRABId)
            ->where('no_rab', 'like', "RAB/{$kodeTipe}/%") // cari semua dengan kode tipe ini, semua tanggal
            ->orderByDesc('no_rab')
            ->first();

        if ($last && preg_match('/\/(\d{5})$/', $last->no_rab, $m)) {
            $urut = str_pad(((int)$m[1]) + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $urut = '00001';
        }

        return "{$prefix}{$urut}";
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tipeRAB()
    {
        return $this->belongsTo(TipeRab::class, 'tipe_rab_id');
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
    public function dinasActivities()
    {
        return $this->hasMany(PengajuanDinasActivity::class);
    }
    public function dinasPersonils()
    {
        return $this->hasMany(PengajuanDinasPersonil::class, 'pengajuan_id');
    }
    public function lampiran()
    {
        return $this->hasOne(\App\Models\Lampiran::class);
    }
    public function lampiranAssets()
    {
        return $this->hasMany(\App\Models\LampiranAsset::class);
    }
    public function lampiranDinas()
    {
        return $this->hasMany(LampiranDinas::class);
    }
    public function persetujuanApprovers()
    {
        return $this->hasMany(\App\Models\PersetujuanApprover::class, 'pengajuan_id');
    }
}
