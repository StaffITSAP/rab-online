<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <style>
        /* dasar */
        body {
            font-family: sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        td,
        th {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 9px;
            vertical-align: top;
            line-height: 1.15;
        }

        .logo-cell {
            width: 8%;
            padding: 2px;
            text-align: center;
            vertical-align: middle;
        }

        .logo-cell img {
            width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .keterangan-table th {
            background: #eee;
        }

        .item-table td,
        .item-table th {
            word-break: break-word;
            white-space: normal;
        }

        .item-table tbody tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        .note-table {
            width: 100%;
            margin-top: 12px;
            border: 1px solid #444;
            border-collapse: collapse;
            font-size: 9px;
        }

        .note-table td {
            border: none;
            padding: 4px 8px;
            vertical-align: top;
        }

        .note-title {
            width: 60px;
            font-weight: bold;
            white-space: nowrap;
        }

        .ttd {
            text-align: center;
            vertical-align: top;
            height: 100px;
            font-size: 12px;
        }

        .ttd-table,
        .ttd-table td {
            border: none;
        }

        .footer-note {
            font-size: 9px;
            margin-top: 10px;
        }

        .page-break {
            page-break-before: always;
        }

        /* lampiran */
        .section-table {
            width: 100%;
            margin-top: 12px;
            border: 1px solid #444;
            border-collapse: collapse;
            font-size: 9px;
        }

        .section-table th,
        .section-table td {
            border: 1px solid #444;
            padding: 2px 4px;
            vertical-align: top;
        }

        .section-table th {
            background: #eee;
            font-size: 8px;
        }

        .section-table td {
            font-size: 8px;
            line-height: 1.1;
        }

        .lampiran-image {
            max-height: 200px;
            max-width: 100%;
            object-fit: contain;
            margin-top: 5px;
            border: 1px solid #ccc;
        }

        .download-link {
            color: #06c;
            text-decoration: underline;
            font-size: 8px;
            display: inline-block;
            margin-top: 2px;
        }

        /* ===== Watermark untuk status expired (merah) ===== */
        .wm-expired {
            position: fixed;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            font-size: 120px;
            font-weight: 800;
            color: rgba(220, 0, 0, 0.18);
            letter-spacing: 8px;
            z-index: 9999;
            pointer-events: none;
            text-transform: uppercase;
            white-space: nowrap;
        }

        /* ===== Watermark untuk status menunggu (kuning) ===== */
        .wm-menunggu {
            position: fixed;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            font-size: 120px;
            font-weight: 800;
            color: rgba(255, 193, 7, 0.20);
            letter-spacing: 8px;
            z-index: 9999;
            pointer-events: none;
            text-transform: uppercase;
            white-space: nowrap;
        }

        /* ===== Watermark untuk status ditolak (merah tegas) ===== */
        .wm-ditolak {
            position: fixed;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            font-size: 120px;
            font-weight: 800;
            color: rgba(200, 0, 0, 0.25);
            letter-spacing: 8px;
            z-index: 9999;
            pointer-events: none;
            text-transform: uppercase;
            white-space: nowrap;
        }

        /* Saat print, buat lebih pekat supaya tetap terbaca */
        @media print {
            .wm-expired {
                color: rgba(220, 0, 0, 0.28);
            }

            .wm-menunggu {
                color: rgba(255, 193, 7, 0.35);
            }

            .wm-ditolak {
                color: rgba(200, 0, 0, 0.35);
            }
        }

        /* Tabel pajak */
        .pajak-table {
            width: 50%;
            margin-left: auto;
            margin-top: 10px;
            border-collapse: collapse;
        }

        .pajak-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 9px;
        }

        .pajak-label {
            text-align: left;
            font-weight: bold;
        }

        .pajak-value {
            text-align: right;
        }

        /* Tabel deskripsi biaya */
        .deskripsi-table {
            width: 100%;
            margin-top: 12px;
            border-collapse: collapse;
        }

        .deskripsi-table th {
            background: #eee;
            text-align: left;
            padding: 4px 6px;
            border: 1px solid #000;
        }

        .deskripsi-table td {
            padding: 4px 6px;
            border: 1px solid #000;
            text-align: justify;
        }

        /* Gaya khusus untuk tabel service order dan item */
        .service-order-cell {
            text-align: center;
            white-space: normal;
            word-wrap: break-word;
        }

        .item-cell {
            text-align: center;
            white-space: normal;
            word-wrap: break-word;
            vertical-align: middle;
        }

        .table-header {
            text-align: center;
            font-weight: bold;
            background: #eee;
        }

        .merged-cell {
            text-align: center;
            vertical-align: middle;
        }
    </style>
</head>

<body>
    @php
    $showSignature = $showSignature ?? true;

    $companyKey = strtolower($pengajuan->company ?? '');
    $companyName = match ($companyKey) {
    'sap' => 'CV Solusi Arya Prima',
    'dinatek' => 'CV Dinatek Jaya Lestari',
    'ssm' => 'PT Sinergi Subur Makmur',
    default => '-',
    };
    $logoMap = [
    'sap' => public_path('logo-sap.png'),
    'dinatek' => public_path('logo-dinatek.png'),
    'ssm' => public_path('logo-ssm.png'),
    ];
    $logoPath = $logoMap[$companyKey] ?? public_path('logo-default.png');

    $lampiranAssets = collect($pengajuan->lampiranAssets ?? []);

    function isImagePath($filePath) {
    $ext = strtolower(pathinfo($filePath ?? '', PATHINFO_EXTENSION));
    return in_array($ext, ['jpg','jpeg','png','gif','webp','bmp']);
    }

    // Hitung total dengan PPh fix 2%
    $pphPersen = 2; // PPh fix 2%
    $totalSubtotal = collect($pengajuan->pengajuan_biaya_services ?? [])->sum('subtotal');
    $totalPph = $totalSubtotal * ($pphPersen / 100);
    $totalDpp = $totalSubtotal - $totalPph;
    $totalBiaya = $totalSubtotal;

    // Kelompokkan item berdasarkan nama barang untuk merge
    $groupedItems = [];
    if (!empty($pengajuan->pengajuan_biaya_services)) {
    foreach ($pengajuan->pengajuan_biaya_services as $item) {
    $itemName = optional($item->service_item)->nama_barang ?? '-';
    if (!isset($groupedItems[$itemName])) {
    $groupedItems[$itemName] = [];
    }
    $groupedItems[$itemName][] = $item;
    }
    }
    @endphp

    {{-- ===== Watermark sesuai status ===== --}}
    @php $status = strtolower($pengajuan->status ?? ''); @endphp

    @if ($status === 'expired')
    <div class="wm-expired">EXPIRED</div>
    @elseif ($status === 'menunggu')
    <div class="wm-menunggu">MENUNGGU</div>
    @elseif ($status === 'ditolak')
    <div class="wm-ditolak">DITOLAK</div>
    @endif

    <h2 align="center" style="margin:5px 0;">FORM PENGAJUAN BIAYA SERVICE</h2>
    <h3 align="center" style="margin:5px 0;">No RAB : {{ strtoupper($pengajuan->no_rab ?? '') }}</h3>
    <h2 align="center" style="margin:5px 0;">{{ $companyName }}</h2>
    <h3 align="center" style="margin:5px 0;">CABANG {{ strtoupper(optional($pengajuan->user?->userStatus?->cabang)->kode ?? '-') }}</h3>

    <table style="font-size:9px;">
        <tr>
            <td rowspan="5" class="logo-cell">
                <img src="{{ $logoPath }}" alt="Logo" style="height:60px; width:100px; margin-bottom:10px;">
            </td>
            <td colspan="2">
                Kantor Pusat : Jl. S. Parman 47 Semarang 50232<br>
                Telp : +6224 8508899<br>
                Fax : (024) 8500599
            </td>
        </tr>
        <tr>
            <td>Tel : {{ optional($pengajuan->user)->telp ?? '-' }}</td>
            <td>Email : {{ optional($pengajuan->user)->email ?? '-' }}</td>
        </tr>
        <tr>
            <td>Fax : -</td>
            <td>Cellular : {{ optional($pengajuan->user)->no_hp ?? '-' }}</td>
        </tr>
        <tr>
            <td>
                Tanggal Dibuat :
                {{ $pengajuan->created_at ? \Carbon\Carbon::parse($pengajuan->created_at)->translatedFormat('d F Y') : '' }}
            </td>
            <td><strong>Nama Pemohon : {{ optional($pengajuan->user)->name ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td></td>
            <td> &nbsp; <strong>Rp</strong>
                <span style="float:left;">Total : </span>
                <strong>{{ number_format((int)($totalBiaya ?? 0), 0, ',', '.') }}</strong>
            </td>
        </tr>
    </table>

    <!-- ===== TABEL BIAYA SERVICE ===== -->
    <table class="keterangan-table item-table" style="font-size:9px; border-collapse:collapse; width:100%; table-layout:fixed;">
        <thead>
            <tr>
                <th style="width:5%;" class="table-header">NO</th>
                <th style="width:20%;" class="table-header">NO. SERVICE ORDER</th>
                <th style="width:25%;" class="table-header">ITEM/SPAREPART</th>
                <th style="width:5%;" class="table-header">QTY</th>
                <th style="width:15%;" class="table-header">HARGA SATUAN</th>
                <th style="width:15%;" class="table-header">SUBTOTAL</th>
                <th style="width:15%;" class="table-header">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @php $counter = 1; @endphp
            @forelse ($groupedItems as $itemName => $items)
            @if(count($items) > 1)
            <!-- Baris pertama untuk item yang digabungkan -->
            <tr>
                <td align="center">{{ $counter }}</td>
                <td class="service-order-cell">
                    {{ optional($items[0]->service)->nomer_so ?? '-' }}
                </td>
                <td rowspan="{{ count($items) }}" class="item-cell merged-cell">
                    {{ $itemName }}
                </td>
                <td align="center" style="white-space:nowrap;">
                    {{ $items[0]->jumlah ?? 0 }}
                </td>
                <td style="padding:4px 6px; text-align:right; vertical-align:top; white-space:nowrap;">
                    <span style="float:left;">Rp</span>
                    {{ number_format((int)($items[0]->harga_satuan ?? 0), 0, ',', '.') }}
                </td>
                <td style="padding:4px 6px; text-align:right; vertical-align:top; white-space:nowrap;">
                    <span style="float:left;">Rp</span>
                    {{ number_format((int)($items[0]->subtotal ?? 0), 0, ',', '.') }}
                </td>
                <td style="padding:4px 6px; text-align:right; vertical-align:top; white-space:nowrap;">
                    <span style="float:left;">Rp</span>
                    {{ number_format((int)($items[0]->subtotal ?? 0), 0, ',', '.') }}
                </td>
            </tr>
            <!-- Baris tambahan untuk item yang sama -->
            @for($i = 1; $i < count($items); $i++)
                <tr>
                <td align="center">{{ $counter + $i }}</td>
                <td class="service-order-cell">
                    {{ optional($items[$i]->service)->nomer_so ?? '-' }}
                </td>
                <td align="center" style="white-space:nowrap;">
                    {{ $items[$i]->jumlah ?? 0 }}
                </td>
                <td style="padding:4px 6px; text-align:right; vertical-align:top; white-space:nowrap;">
                    <span style="float:left;">Rp</span>
                    {{ number_format((int)($items[$i]->harga_satuan ?? 0), 0, ',', '.') }}
                </td>
                <td style="padding:4px 6px; text-align:right; vertical-align:top; white-space:nowrap;">
                    <span style="float:left;">Rp</span>
                    {{ number_format((int)($items[$i]->subtotal ?? 0), 0, ',', '.') }}
                </td>
                <td style="padding:4px 6px; text-align:right; vertical-align:top; white-space:nowrap;">
                    <span style="float:left;">Rp</span>
                    {{ number_format((int)($items[$i]->subtotal ?? 0), 0, ',', '.') }}
                </td>
                </tr>
                @endfor
                @php $counter += count($items); @endphp
                @else
                <!-- Baris dengan item tunggal -->
                <tr>
                    <td align="center">{{ $counter }}</td>
                    <td class="service-order-cell">
                        {{ optional($items[0]->service)->nomer_so ?? '-' }}
                    </td>
                    <td class="item-cell">
                        {{ $itemName }}
                    </td>
                    <td align="center" style="white-space:nowrap;">
                        {{ $items[0]->jumlah ?? 0 }}
                    </td>
                    <td style="padding:4px 6px; text-align:right; vertical-align:top; white-space:nowrap;">
                        <span style="float:left;">Rp</span>
                        {{ number_format((int)($items[0]->harga_satuan ?? 0), 0, ',', '.') }}
                    </td>
                    <td style="padding:4px 6px; text-align:right; vertical-align:top; white-space:nowrap;">
                        <span style="float:left;">Rp</span>
                        {{ number_format((int)($items[0]->subtotal ?? 0), 0, ',', '.') }}
                    </td>
                    <td style="padding:4px 6px; text-align:right; vertical-align:top; white-space:nowrap;">
                        <span style="float:left;">Rp</span>
                        {{ number_format((int)($items[0]->subtotal ?? 0), 0, ',', '.') }}
                    </td>
                </tr>
                @php $counter++; @endphp
                @endif
                @empty
                <tr>
                    <td colspan="7" align="center">Belum ada data biaya service.</td>
                </tr>
                @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" align="center"><strong>TOTAL</strong></td>
                <td style="padding:4px 6px; text-align:right; vertical-align:top; white-space:nowrap;">
                    <span style="float:left;"><strong>Rp</strong></span>
                    <strong>{{ number_format((int)$totalBiaya, 0, ',', '.') }}</strong>
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- ===== TABEL PAJAK (PPh, PPh Nominal, DPP Jual) ===== -->
    <table class="pajak-table">
        <tr>
            <td class="pajak-label">PPh %</td>
            <td class="pajak-value">{{ $pphPersen }}%</td>
        </tr>
        <tr>
            <td class="pajak-label">Total PPh</td>
            <td class="pajak-value">Rp {{ number_format((int)$totalPph, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="pajak-label">Total DPP Jual</td>
            <td class="pajak-value">Rp {{ number_format((int)$totalDpp, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="pajak-label">Total Biaya</td>
            <td class="pajak-value">Rp {{ number_format((int)$totalBiaya, 0, ',', '.') }}</td>
        </tr>
    </table>

    <!-- ===== TABEL DESKRIPSI BIAYA ===== -->
    @if(collect($pengajuan->pengajuan_biaya_services ?? [])->where('deskripsi','!=',null)->where('deskripsi','!=','')->count() > 0)
    <table class="deskripsi-table">
        <thead>
            <tr>
                <th style="width:8%;">NO</th>
                <th style="width:92%;">DESKRIPSI BIAYA</th>
            </tr>
        </thead>
        <tbody>
            @foreach (($pengajuan->pengajuan_biaya_services ?? []) as $item)
            @if(trim($item->deskripsi ?? '') !== '')
            <tr>
                <td align="center">{{ $loop->iteration }}</td>
                <td style="text-align:justify;">{{ $item->deskripsi }}</td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- ===== NOTE ===== -->
    <table class="note-table">
        <tr>
            <td class="note-title">Note:</td>
            <td>
                <ul style="margin:0; padding-left:18px;">
                    <li>Biaya service harus sesuai dengan perjanjian dan kesepakatan dengan customer.</li>
                    <li>Pastikan semua biaya telah diverifikasi dan disetujui oleh pihak yang berwenang.</li>
                    <li>PPh ditetapkan sebesar 2% dari total biaya.</li>
                </ul>
            </td>
        </tr>
    </table>

    {{-- ====== KETERANGAN ====== --}}
    @if(isset($pengajuan->keterangan) && trim($pengajuan->keterangan) !== '')
    <div style="margin-top:12px; font-size:9px;">
        <strong>Keterangan:</strong><br>
        <div style="margin-top:4px; text-align:justify;">
            {{ $pengajuan->keterangan }}
        </div>
    </div>
    @endif

    <p align="center" style="font-size:12px;">
        {{ optional(optional($pengajuan->user)->userStatus)->kota ?? 'Kota Tidak Diketahui' }},
        {{ $pengajuan->created_at ? \Carbon\Carbon::parse($pengajuan->created_at)->translatedFormat('d F Y') : '' }}
    </p>

    <!-- ===== TANDA TANGAN ===== -->
    <table class="ttd-table" style="margin-top:10px; width:100%;">
        <tr>
            <td class="ttd">
                Yang Mengajukan <br><br>
                @if ($showSignature && optional(optional($pengajuan->user)->userStatus)->signature_path)
                <img src="{{ public_path('storage/' . optional(optional($pengajuan->user)->userStatus)->signature_path) }}" height="60"><br><br>
                @else
                <br><br><br><br><br>
                @endif
                {{ optional($pengajuan->user)->name ?? '' }}<br>
                <strong>{{ optional(optional($pengajuan->user)->userStatus)->divisi->nama ?? '' }}</strong><br>
                {{ $pengajuan->created_at ? \Carbon\Carbon::parse($pengajuan->created_at)->translatedFormat('d F Y H:i') : '' }}
            </td>

            @foreach (($pengajuan->statuses ?? []) as $status)
            @if ($status->is_approved ?? false)
            <td class="ttd">
                Menyetujui<br><br>
                @if ($showSignature && optional(optional($status->user)->userStatus)->signature_path)
                <img src="{{ public_path('storage/' . optional(optional($status->user)->userStatus)->signature_path) }}" height="60"><br><br>
                @else
                <br><br><br><br><br>
                @endif
                {{ optional($status->user)->name ?? '' }}<br>
                <strong>{{ optional(optional($status->user)->userStatus)->divisi->nama ?? '' }}</strong><br>
                {{ $status->approved_at ? \Carbon\Carbon::parse($status->approved_at)->translatedFormat('d F Y H:i') : '' }}
            </td>
            @endif
            @endforeach
        </tr>
    </table>

    {{-- ====== CATATAN / ALASAN SEMUA STATUS ====== --}}
    @php
    $statusLogs = $pengajuan->statuses()
    ->whereNotNull('is_approved')
    ->with('user')
    ->orderBy('approved_at')
    ->get()
    ->filter(function ($log) {
    return $log->is_approved ? $log->catatan_approve : $log->alasan_ditolak;
    });
    @endphp

    @if ($statusLogs->isNotEmpty())
    <div style="margin-top:15px; font-size:10px; border:1px solid #ccc; padding:6px 8px; background:#f9f9f9;">
        <strong>Catatan :</strong>
        <ul style="margin:6px 0 0 15px; padding:0; font-size:9px; line-height:1.5;">
            @foreach ($statusLogs as $log)
            @php
            $jenis = $log->is_approved ? 'Disetujui' : 'Ditolak';
            $nama = $log->user?->name ?? '-';
            $isi = $log->is_approved ? $log->catatan_approve : $log->alasan_ditolak;
            @endphp
            <li>
                {{ $isi }}
                <span style="color:#555;"> ({{ $jenis }}, {{ $nama }})</span>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- ===== LAMPIRAN (halaman baru) ===== -->
    @if ($lampiranAssets->count())
    <div class="page-break">
        <h2 align="center" style="margin:20px 0 10px;">LAMPIRAN PENGAJUAN BIAYA SERVICE</h2>
        <h3 align="center" style="margin:5px 0 20px;">No RAB : {{ strtoupper($pengajuan->no_rab ?? '') }}</h3>

        <table class="section-table">
            <thead>
                <tr>
                    <th style="width:5%;">NO</th>
                    <th style="width:35%;">NAMA LAMPIRAN</th>
                    <th style="width:60%;">FILE / PREVIEW</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($lampiranAssets as $lamp)
                @php $filePath = $lamp->file_path ?? ''; @endphp
                <tr>
                    <td align="center">{{ $loop->iteration }}</td>
                    <td style="text-align:justify;">
                        <strong>{{ $lamp->original_name ?? '-' }}</strong>
                        <div style="font-size:8px; color:#666; margin-top:2px;">
                            File: {{ basename($filePath) }}<br>
                        </div>
                    </td>
                    <td style="text-align:center;">
                        @if ($filePath)
                        @if (isImagePath($filePath))
                        <img src="{{ public_path('storage/' . $filePath) }}" alt="Lampiran" class="lampiran-image">
                        <br><small style="color:#666;">{{ basename($filePath) }}</small>
                        @else
                        <div style="padding:20px; border:2px dashed #ccc; background:#f9f9f9; color:#666;">
                            <strong>{{ strtoupper(pathinfo($filePath, PATHINFO_EXTENSION)) }} FILE</strong><br>
                            <small>{{ basename($filePath) }}</small>
                        </div>
                        @endif
                        @else
                        <div style="color:#999; padding:20px;">File tidak tersedia</div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top:30px; text-align:center; color:#666; font-size:8px;">
            <hr style="border:none; border-top:1px solid #ccc; margin:20px 0;">
            Halaman Lampiran - {{ $companyName }}<br>
            Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }}
        </div>
    </div>
    @endif

</body>

</html>