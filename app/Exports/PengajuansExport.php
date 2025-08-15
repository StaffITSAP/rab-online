<?php

namespace App\Exports;

use App\Models\Pengajuan;
use App\Exports\Support\SimpleArraySheet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PengajuansExport implements WithMultipleSheets
{
    /** @var array<string,mixed>|null */
    protected ?array $filters;

    /** Scope halaman khusus */
    protected bool $onlyMobil    = false; // use_car=1 OR use_pengiriman=1
    protected bool $onlyTeknisi  = false; // menggunakan_teknisi=1

    /** @var \Illuminate\Support\Collection<int,\App\Models\Pengajuan> */
    protected Collection $records;

    /**
     * @param array<string,mixed>|null $filters
     * @param bool $onlyMobil    Jika true: where (use_car=1 OR use_pengiriman=1)
     * @param bool $onlyTeknisi  Jika true: where (menggunakan_teknisi=1)
     */
    public function __construct(?array $filters = null, bool $onlyMobil = false, bool $onlyTeknisi = false)
    {
        $this->filters     = $filters;
        $this->onlyMobil   = $onlyMobil;
        $this->onlyTeknisi = $onlyTeknisi;

        $query = Pengajuan::query()
            ->with([
                'user',
                'tipeRAB',
                // Asset / Dinas
                'pengajuan_assets',
                'pengajuan_dinas',
                'dinasActivities',
                'dinasPersonils',
                // Status / Approver
                'statuses.user',
                'persetujuanApprovers',
                // Lampiran umum
                'lampiran',
                'lampiranAssets',
                'lampiranDinas',
                // Marcomm
                'pengajuan_marcomm_promosis',
                'lampiranPromosi',
                'pengajuan_marcomm_kebutuhans',
                'marcommKebutuhanAmplops',
                'marcommKebutuhanKartus',
                'marcommKebutuhanKemejas',
                'lampiranKebutuhan',
                'marcommKegiatans',
                'lampiranKegiatan',
                'marcommKegiatanPusats',
                'marcommKegiatanCabangs',
            ]);

        // === Scope dasar mengikuti halaman ===
        if ($this->onlyMobil) {
            $query->where(function ($q) {
                $q->where('use_car', 1)->orWhere('use_pengiriman', 1);
            });
        }
        if ($this->onlyTeknisi) {
            $query->where('menggunakan_teknisi', 1);
        }

        // Terapkan filter dari Filament (jika ada)
        $this->applyFilters($query, $this->filters);

        $this->records = $query->orderByDesc('created_at')->get();
    }

    public function sheets(): array
    {
        // ===== Sheet Utama: Pengajuans
        $mainHead = [
            'ID',
            'No RAB',
            'Pemohon',
            'Tipe RAB',
            'Status',
            'Expired Unlocked',
            'Lokasi',
            'Keterangan',
            'Menggunakan Teknisi',
            'Asset Teknisi',
            'Use Pengiriman',
            'Use Car',
            'Total Biaya',
            'Tgl Realisasi',
            'Tgl Pulang',
            'Jam',
            'Jml Personil',
            'Created At',
            'Updated At'
        ];

        $mainRows = $this->records->map(function (Pengajuan $p) {
            return [
                $p->id,
                $p->no_rab,
                $p->user?->name,
                $p->tipeRAB?->nama,
                $p->status,
                $p->expired_unlocked ? 'Ya' : 'Tidak',
                $p->lokasi,
                $p->keterangan,
                $p->menggunakan_teknisi ? 'Ya' : 'Tidak',
                $p->asset_teknisi ? 'Ya' : 'Tidak',
                $p->use_pengiriman ? 'Ya' : 'Tidak',
                $p->use_car ? 'Ya' : 'Tidak',
                (int) $p->total_biaya,
                optional($p->tgl_realisasi)?->format('Y-m-d'),
                optional($p->tgl_pulang)?->format('Y-m-d'),
                $p->jam,
                (int) $p->jml_personil,
                optional($p->created_at)?->format('Y-m-d H:i:s'),
                optional($p->updated_at)?->format('Y-m-d H:i:s'),
            ];
        })->all();

        $sheets = [
            new SimpleArraySheet('Pengajuans', $mainHead, $mainRows),
        ];

        // ===== Sheet Relasi: Dinas
        $headDinas = ['Pengajuan ID', 'Dinas ID', 'Deskripsi', 'Keterangan', 'PIC', 'Jml Hari', 'Harga Satuan', 'Subtotal', 'Created At'];
        $rowsDinas = $this->records->flatMap(
            fn(Pengajuan $p) =>
            $p->pengajuan_dinas->map(fn($d) => [
                $p->id,
                $d->id,
                $d->deskripsi,
                $d->keterangan,
                $d->pic,
                $d->jml_hari,
                $d->harga_satuan,
                $d->subtotal,
                optional($d->created_at)?->format('Y-m-d H:i:s'),
            ])
        )->all();
        $sheets[] = new SimpleArraySheet('Dinas', $headDinas, $rowsDinas);

        // Dinas Activities
        $headAct = ['Pengajuan ID', 'Activity ID', 'No Activity', 'Nama Dinas', 'Keterangan', 'Created At'];
        $rowsAct = $this->records->flatMap(
            fn(Pengajuan $p) =>
            $p->dinasActivities->map(fn($a) => [
                $p->id,
                $a->id,
                $a->no_activity,
                $a->nama_dinas,
                $a->keterangan,
                optional($a->created_at)?->format('Y-m-d H:i:s'),
            ])
        )->all();
        $sheets[] = new SimpleArraySheet('Dinas Activities', $headAct, $rowsAct);

        // Dinas Personils
        $headPers = ['Pengajuan ID', 'Personil ID', 'Nama Personil', 'Created At'];
        $rowsPers = $this->records->flatMap(
            fn(Pengajuan $p) =>
            $p->dinasPersonils->map(fn($r) => [
                $p->id,
                $r->id,
                $r->nama_personil,
                optional($r->created_at)?->format('Y-m-d H:i:s'),
            ])
        )->all();
        $sheets[] = new SimpleArraySheet('Dinas Personils', $headPers, $rowsPers);

        // Assets
        $headAssets = ['Pengajuan ID', 'Asset ID', 'Nama Barang', 'Tipe', 'Jumlah', 'Keperluan', 'Harga Unit', 'Subtotal', 'Keterangan', 'Created At'];
        $rowsAssets = $this->records->flatMap(
            fn(Pengajuan $p) =>
            $p->pengajuan_assets->map(fn($a) => [
                $p->id,
                $a->id,
                $a->nama_barang,
                $a->tipe_barang,
                $a->jumlah,
                $a->keperluan,
                $a->harga_unit,
                $a->subtotal,
                $a->keterangan,
                optional($a->created_at)?->format('Y-m-d H:i:s'),
            ])
        )->all();
        $sheets[] = new SimpleArraySheet('Assets', $headAssets, $rowsAssets);

        // Statuses
        $headStat = ['Pengajuan ID', 'Status ID', 'User', 'Is Approved', 'Approved At', 'Alasan Ditolak', 'Catatan Approve', 'Created At'];
        $rowsStat = $this->records->flatMap(
            fn(Pengajuan $p) =>
            $p->statuses->map(fn($s) => [
                $p->id,
                $s->id,
                $s->user?->name,
                is_null($s->is_approved) ? null : ($s->is_approved ? 'Ya' : 'Tidak'),
                optional($s->approved_at)?->format('Y-m-d H:i:s'),
                $s->alasan_ditolak,
                $s->catatan_approve,
                optional($s->created_at)?->format('Y-m-d H:i:s'),
            ])
        )->all();
        $sheets[] = new SimpleArraySheet('Statuses', $headStat, $rowsStat);

        // Lampiran flag
        $headLamp = ['Pengajuan ID', 'Lampiran ID', 'Asset', 'Dinas', 'Marcomm Kegiatan', 'Marcomm Kebutuhan', 'Marcomm Promosi', 'Created At'];
        $rowsLamp = $this->records->map(function (Pengajuan $p) {
            $l = $p->lampiran;
            return $l ? [
                $p->id,
                $l->id,
                $l->lampiran_asset ? 'Ya' : 'Tidak',
                $l->lampiran_dinas ? 'Ya' : 'Tidak',
                $l->lampiran_marcomm_kegiatan ? 'Ya' : 'Tidak',
                $l->lampiran_marcomm_kebutuhan ? 'Ya' : 'Tidak',
                $l->lampiran_marcomm_promosi ? 'Ya' : 'Tidak',
                optional($l->created_at)?->format('Y-m-d H:i:s'),
            ] : [$p->id, null, null, null, null, null, null, null];
        })->all();
        $sheets[] = new SimpleArraySheet('Lampirans', $headLamp, $rowsLamp);

        // Lampiran detail
        $sheets[] = new SimpleArraySheet(
            'Lampiran Assets',
            ['Pengajuan ID', 'ID', 'Original Name', 'Path', 'Created At'],
            $this->rowsFrom($this->records, 'lampiranAssets', fn($r, $x) => [$r->id, $x->id, $x->original_name, $x->file_path, optional($x->created_at)?->format('Y-m-d H:i:s')])
        );
        $sheets[] = new SimpleArraySheet(
            'Lampiran Dinas',
            ['Pengajuan ID', 'ID', 'Original Name', 'Path', 'Created At'],
            $this->rowsFrom($this->records, 'lampiranDinas', fn($r, $x) => [$r->id, $x->id, $x->original_name, $x->file_path, optional($x->created_at)?->format('Y-m-d H:i:s')])
        );

        // Marcomm Promosi
        $sheets[] = new SimpleArraySheet(
            'Marcomm Promosi',
            ['Pengajuan ID', 'ID', 'Deskripsi', 'Qty', 'Harga Satuan', 'Subtotal', 'Created At'],
            $this->rowsFrom($this->records, 'pengajuan_marcomm_promosis', fn($r, $x) => [$r->id, $x->id, $x->deskripsi, $x->qty, $x->harga_satuan, $x->subtotal, optional($x->created_at)?->format('Y-m-d H:i:s')])
        );

        // Marcomm Kebutuhan (master + detail)
        $sheets[] = new SimpleArraySheet(
            'Marcomm Kebutuhan',
            ['Pengajuan ID', 'ID', 'Deskripsi', 'Qty', 'Harga Satuan', 'Subtotal', 'Tipe', 'Total Amplop', 'Kebutuhan Amplop', 'Kartu', 'Kemeja', 'Created At'],
            $this->rowsFrom($this->records, 'pengajuan_marcomm_kebutuhans', fn($r, $x) => [
                $r->id,
                $x->id,
                $x->deskripsi,
                $x->qty,
                $x->harga_satuan,
                $x->subtotal,
                $x->tipe,
                $x->total_amplop,
                $x->kebutuhan_amplop ? 'Ya' : 'Tidak',
                $x->kebutuhan_kartu ? 'Ya' : 'Tidak',
                $x->kebutuhan_kemeja ? 'Ya' : 'Tidak',
                optional($x->created_at)?->format('Y-m-d H:i:s')
            ])
        );
        $sheets[] = new SimpleArraySheet(
            'Kebutuhan Amplops',
            ['Pengajuan ID', 'ID', 'Cabang', 'Jumlah', 'Created At'],
            $this->rowsFrom($this->records, 'marcommKebutuhanAmplops', fn($r, $x) => [$r->id, $x->id, $x->cabang, $x->jumlah, optional($x->created_at)?->format('Y-m-d H:i:s')])
        );
        $sheets[] = new SimpleArraySheet(
            'Kebutuhan Kartus',
            ['Pengajuan ID', 'ID', 'Kartu Nama', 'ID Card', 'Created At'],
            $this->rowsFrom($this->records, 'marcommKebutuhanKartus', fn($r, $x) => [$r->id, $x->id, $x->kartu_nama, $x->id_card, optional($x->created_at)?->format('Y-m-d H:i:s')])
        );
        $sheets[] = new SimpleArraySheet(
            'Kebutuhan Kemejas',
            ['Pengajuan ID', 'ID', 'Nama', 'Ukuran', 'Created At'],
            $this->rowsFrom($this->records, 'marcommKebutuhanKemejas', fn($r, $x) => [$r->id, $x->id, $x->nama, $x->ukuran, optional($x->created_at)?->format('Y-m-d H:i:s')])
        );

        // Marcomm Kegiatan + Tim
        $sheets[] = new SimpleArraySheet(
            'Marcomm Kegiatan',
            ['Pengajuan ID', 'ID', 'Deskripsi', 'Keterangan', 'PIC', 'Jml Hari', 'Harga Satuan', 'Subtotal', 'Tim Pusat', 'Tim Cabang', 'Created At'],
            $this->rowsFrom($this->records, 'marcommKegiatans', fn($r, $x) => [
                $r->id,
                $x->id,
                $x->deskripsi,
                $x->keterangan,
                $x->pic,
                $x->jml_hari,
                $x->harga_satuan,
                $x->subtotal,
                $x->tim_pusat ? 'Ya' : 'Tidak',
                $x->tim_cabang ? 'Ya' : 'Tidak',
                optional($x->created_at)?->format('Y-m-d H:i:s')
            ])
        );
        $sheets[] = new SimpleArraySheet(
            'Kegiatan Pusat',
            ['Pengajuan ID', 'ID', 'Nama', 'Gender', 'Created At'],
            $this->rowsFrom($this->records, 'marcommKegiatanPusats', fn($r, $x) => [$r->id, $x->id, $x->nama, $x->gender, optional($x->created_at)?->format('Y-m-d H:i:s')])
        );
        $sheets[] = new SimpleArraySheet(
            'Kegiatan Cabang',
            ['Pengajuan ID', 'ID', 'Cabang', 'Nama', 'Gender', 'Created At'],
            $this->rowsFrom($this->records, 'marcommKegiatanCabangs', fn($r, $x) => [$r->id, $x->id, $x->cabang, $x->nama, $x->gender, optional($x->created_at)?->format('Y-m-d H:i:s')])
        );

        return $sheets;
    }

    /**
     * Terapkan filter sesuai definisi di Resource.
     * Struktur `$filters` mengikuti query string Filament v3 (`tableFilters`)
     */
    protected function applyFilters(Builder $q, ?array $filters): void
    {
        if (! $filters) return;

        // Status (SelectFilter)
        if ($val = Arr::get($filters, 'status.value')) {
            $q->where('status', $val);
        }

        // Tanggal Dibuat
        $from = Arr::get($filters, 'tgl_dibuat_range.dari');
        $to   = Arr::get($filters, 'tgl_dibuat_range.sampai');
        if ($from && $to) {
            $q->whereBetween('created_at', [
                date('Y-m-d 00:00:00', strtotime($from)),
                date('Y-m-d 23:59:59', strtotime($to)),
            ]);
        } elseif ($from) {
            $q->whereDate('created_at', '>=', $from);
        } elseif ($to) {
            $q->whereDate('created_at', '<=', $to);
        }

        // Tanggal Realisasi
        $from = Arr::get($filters, 'tgl_realisasi_range.dari');
        $to   = Arr::get($filters, 'tgl_realisasi_range.sampai');
        if ($from && $to) {
            $q->whereBetween('tgl_realisasi', [
                date('Y-m-d 00:00:00', strtotime($from)),
                date('Y-m-d 23:59:59', strtotime($to)),
            ]);
        } elseif ($from) {
            $q->whereDate('tgl_realisasi', '>=', $from);
        } elseif ($to) {
            $q->whereDate('tgl_realisasi', '<=', $to);
        }

        // Ternary filters (tambahkan 'use_pengiriman' juga agar mendukung halaman Mobil)
        foreach (['menggunakan_teknisi', 'use_car', 'use_pengiriman', 'expired_unlocked'] as $boolKey) {
            $tern = Arr::get($filters, $boolKey . '.value', null);
            if ($tern === true || $tern === 1 || $tern === '1') {
                $q->where($boolKey, 1);
            } elseif ($tern === false || $tern === 0 || $tern === '0') {
                $q->where($boolKey, 0);
            }
        }
    }

    /**
     * Utility: render rows dari relasi hasMany.
     *
     * @param \Illuminate\Support\Collection $recs
     * @param string $relation
     * @param callable $map  fn(Pengajuan $r, $child): array
     * @return array<int,array<int,mixed>>
     */
    protected function rowsFrom(Collection $recs, string $relation, callable $map): array
    {
        return $recs->flatMap(function (Pengajuan $r) use ($relation, $map) {
            $items = $r->{$relation} ?? collect();
            return collect($items)->map(fn($x) => $map($r, $x));
        })->all();
    }
}
