<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'keterangan',
        'lokasi',
    ];

    protected $casts = [
        'menggunakan_teknisi' => 'boolean',
        'use_pengiriman'      => 'boolean',
        'use_car'             => 'boolean',
        'asset_teknisi'       => 'boolean',
    ];

    protected $dates = ['tgl_realisasi', 'tgl_pulang'];

    /**
     * Relasi-relasi yang harus ikut dihapus/restore/forceDelete.
     * Nama diambil dari method relationship pada model ini.
     */
    protected static array $cascadeRelations = [
        // Asset & Dinas
        'pengajuan_assets',
        'pengajuan_dinas',
        'dinasActivities',
        'dinasPersonils',

        // Status & Approver
        'statuses',
        'persetujuanApprovers',

        // Lampiran umum
        'lampiran',
        'lampiranAssets',
        'lampiranDinas',

        // Marcomm Promosi
        'pengajuan_marcomm_promosis',
        'lampiranPromosi',

        // Marcomm Kebutuhan
        'pengajuan_marcomm_kebutuhans',
        'marcommKebutuhanAmplops',
        'marcommKebutuhanKartus',
        'marcommKebutuhanKemejas',
        'lampiranKebutuhan',

        // Marcomm Kegiatan
        'marcommKegiatans',              // alias utama
        'pengajuan_marcomm_kegiatans',   // alias jika masih dipakai
        'lampiranKegiatan',
        'marcommKegiatanPusats',
        'marcommKegiatanCabangs',
    ];

    protected static function booted()
    {
        // Generate nomor RAB saat create
        static::creating(function ($pengajuan) {
            $pengajuan->no_rab = self::generateNoRAB((int) $pengajuan->tipe_rab_id);
        });

        // Auto-expire sederhana
        static::retrieved(function ($pengajuan) {
            if ($pengajuan->status === 'menunggu' && now()->diffInDays($pengajuan->created_at) > 2) {
                $pengajuan->status = 'expired';
                $pengajuan->saveQuietly();
            }
        });

        /**
         * Soft delete child-relations ketika model ini di-soft delete.
         */
        static::deleting(function ($pengajuan) {
            // Jika force delete, biarkan event forceDeleted yang menangani
            if (method_exists($pengajuan, 'isForceDeleting') && $pengajuan->isForceDeleting()) {
                return;
            }

            foreach (self::$cascadeRelations as $relation) {
                if (!method_exists($pengajuan, $relation)) {
                    continue;
                }
                try {
                    $query = $pengajuan->{$relation}();
                    // Ambil koleksi; untuk hasOne juga aman karena ->get() menghasilkan Collection
                    $items = $query->get();
                    $items->each(function ($child) {
                        // Hanya panggil jika model anak punya method delete (umumnya ada)
                        if (method_exists($child, 'delete')) {
                            $child->delete();
                        }
                    });
                } catch (\Throwable $e) {
                    // Abaikan relasi yang bukan Eloquent Relationship / tidak valid
                }
            }
        });

        /**
         * Restore otomatis semua child-relations yang di-soft delete.
         */
        static::restoring(function ($pengajuan) {
            foreach (self::$cascadeRelations as $relation) {
                if (!method_exists($pengajuan, $relation)) {
                    continue;
                }
                try {
                    $query = $pengajuan->{$relation}();
                    // Jika relasi anak pakai SoftDeletes, withTrashed() ada; jika tidak, pakai query biasa.
                    $items = method_exists($query, 'withTrashed')
                        ? $query->withTrashed()->get()
                        : $query->get();

                    $items->each(function ($child) {
                        if (method_exists($child, 'restore')) {
                            $child->restore();
                        }
                    });
                } catch (\Throwable $e) {
                    // Abaikan jika relasi tidak mendukung restore
                }
            }
        });

        /**
         * Ketika benar-benar dihapus permanen, force delete juga semua child-relations.
         */
        static::forceDeleted(function ($pengajuan) {
            foreach (self::$cascadeRelations as $relation) {
                if (!method_exists($pengajuan, $relation)) {
                    continue;
                }
                try {
                    $query = $pengajuan->{$relation}();
                    $items = method_exists($query, 'withTrashed')
                        ? $query->withTrashed()->get()
                        : $query->get();

                    $items->each(function ($child) {
                        if (method_exists($child, 'forceDelete')) {
                            $child->forceDelete();
                        } elseif (method_exists($child, 'delete')) {
                            // fallback: minimal hapus soft-deleted biasa
                            $child->delete();
                        }
                    });
                } catch (\Throwable $e) {
                    // Abaikan jika ada relasi yang bukan model Eloquent
                }
            }
        });
    }

    public static function generateNoRAB(int $tipeRABId): string
    {
        $today   = now();
        $dateStr = $today->format('ymd'); // contoh: 250802

        $tipeRAB  = \App\Models\TipeRab::find($tipeRABId);
        $kodeTipe = $tipeRAB?->kode ?? 'XX';

        $prefix = "RAB/{$kodeTipe}/{$dateStr}/";

        $last = self::withTrashed()
            ->where('tipe_rab_id', $tipeRABId)
            ->where('no_rab', 'like', "RAB/{$kodeTipe}/%")
            ->orderByDesc('no_rab')
            ->first();

        if ($last && preg_match('/\/(\d{5})$/', $last->no_rab, $m)) {
            $urut = str_pad(((int) $m[1]) + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $urut = '00001';
        }

        return "{$prefix}{$urut}";
    }

    // === HELPER: Hitung total by tipe ===
    public function calculateTotalBiaya(): int
    {
        return match ((int) $this->tipe_rab_id) {
            1 => (int) $this->pengajuan_assets()->sum('subtotal'),
            2 => (int) $this->pengajuan_dinas()->sum('subtotal'),
            3 => (int) $this->pengajuan_marcomm_kegiatans()->sum('subtotal'),
            4 => (int) $this->pengajuan_marcomm_promosis()->sum('subtotal'),
            5 => (int) $this->pengajuan_marcomm_kebutuhans()->sum('subtotal'),
            default => 0,
        };
    }

    // ================= Relasi =================

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function tipeRAB()
    {
        return $this->belongsTo(TipeRab::class, 'tipe_rab_id');
    }

    // Asset / Dinas
    public function assets()
    {
        return $this->hasMany(PengajuanAsset::class);
    }
    public function pengajuan_assets()
    {
        return $this->hasMany(PengajuanAsset::class);
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

    // Status / Approver
    public function statuses()
    {
        return $this->hasMany(PengajuanStatus::class);
    }
    public function persetujuanApprovers()
    {
        return $this->hasMany(PersetujuanApprover::class, 'pengajuan_id');
    }

    // Lampiran umum
    public function lampiran()
    {
        return $this->hasOne(Lampiran::class);
    }
    public function lampiranAssets()
    {
        return $this->hasMany(LampiranAsset::class);
    }
    public function lampiranDinas()
    {
        return $this->hasMany(LampiranDinas::class);
    }

    // Marcomm Promosi
    public function pengajuan_marcomm_promosis()
    {
        return $this->hasMany(PengajuanMarcommPromosi::class);
    }
    public function lampiranPromosi()
    {
        return $this->hasMany(LampiranMarcommPromosi::class);
    }

    // Marcomm Kebutuhan
    public function pengajuan_marcomm_kebutuhans()
    {
        return $this->hasMany(PengajuanMarcommKebutuhan::class);
    }
    public function marcommKebutuhanAmplops()
    {
        return $this->hasMany(PengajuanMarcommKebutuhanAmplop::class);
    }
    public function marcommKebutuhanKartus()
    {
        return $this->hasMany(PengajuanMarcommKebutuhanKartu::class);
    }
    public function marcommKebutuhanKemejas()
    {
        return $this->hasMany(PengajuanMarcommKebutuhanKemeja::class);
    }
    public function lampiranKebutuhan()
    {
        return $this->hasMany(LampiranKebutuhan::class);
    }

    // Marcomm Kegiatan
    public function marcommKegiatans()
    {
        return $this->hasMany(PengajuanMarcommKegiatan::class);
    }
    public function pengajuan_marcomm_kegiatans()
    {
        return $this->hasMany(PengajuanMarcommKegiatan::class);
    } // alias
    public function lampiranKegiatan()
    {
        return $this->hasMany(LampiranMarcommKegiatan::class);
    }
    public function marcommKegiatanPusats()
    {
        return $this->hasMany(LampiranMarcommKegiatanPusat::class, 'pengajuan_id');
    }
    public function marcommKegiatanCabangs()
    {
        return $this->hasMany(LampiranMarcommKegiatanCabang::class, 'pengajuan_id');
    }

    // Lain-lain (kompatibilitas)
    public function barangs()
    {
        return $this->hasMany(PengajuanAsset::class);
    } // jika masih dipakai di tempat lain
}
