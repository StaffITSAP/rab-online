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
            /* horizontal */
            vertical-align: middle;
            /* vertical */
        }

        .logo-cell img {
            width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
            /* horizontal center yang robust */


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
    </style>
</head>

<body>
    @php
    $showSignature = $showSignature ?? true;

    $companyKey = strtolower($pengajuan->user->company ?? '');
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
    @endphp

    <h2 align="center" style="margin:5px 0;">FORM PENGAJUAN BARANG INTERN</h2>
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
                <strong>{{ number_format((int)($pengajuan->total_biaya ?? 0), 0, ',', '.') }}</strong>
            </td>
        </tr>
    </table>

    <!-- ===== TABEL BARANG ===== -->
    <table class="keterangan-table item-table" style="font-size:9px;">
        <thead>
            <tr>
                <th style="width:5%;">NO</th>
                <th style="width:25%;">NAMA DAN TIPE BARANG</th>
                <th style="width:25%;">KEPERLUAN</th>
                <th style="width:8%;">QTY</th>
                <th style="width:10%;">SATUAN</th>
                <th style="width:15%;">HARGA SATUAN</th>
                <th style="width:10%;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @forelse (($pengajuan->barangs ?? []) as $item)
            <tr>
                <td align="center">{{ $loop->iteration }}</td>
                <td style="text-align:justify;">{{ $item->nama_barang ?? '-' }}</td>
                <td style="text-align:justify;">{{ $item->keperluan ?? '-' }}</td>
                <td align="center">{{ $item->jumlah ?? 0 }}</td>
                <td align="center">{{ $item->tipe_barang ?? '-' }}</td>

                <!-- Harga Satuan: Rp kiri, angka kanan (inline style, top aligned) -->
                <td style="padding:4px 6px; text-align:right; vertical-align:top; white-space:nowrap;">
                    <span style="float:left;">Rp</span>{{ number_format((int)($item->harga_unit ?? 0), 0, ',', '.') }}
                </td>

                <!-- Total per baris -->
                <td style="padding:4px 6px; text-align:right; vertical-align:top; white-space:nowrap;">
                    <span style="float:left;">Rp</span>{{ number_format((int)($item->subtotal ?? 0), 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" align="center">Belum ada data barang.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" align="center"><strong>TOTAL</strong></td>
                <td style="padding:4px 6px; text-align:right; vertical-align:top; white-space:nowrap;">
                    <span style="float:left;"><strong>Rp</strong></span>
                    <strong>{{ number_format((int)collect($pengajuan->barangs ?? [])->sum('subtotal'), 0, ',', '.') }}</strong>
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- ===== TABEL KETERANGAN (jika ada) ===== -->
    @if(collect($pengajuan->barangs ?? [])->where('keterangan','!=',null)->where('keterangan','!=','')->count() > 0)
    <table class="keterangan-table">
        <thead>
            <tr>
                <th style="width:8%;">NO</th>
                <th style="width:92%;">KETERANGAN</th>
            </tr>
        </thead>
        <tbody>
            @foreach (($pengajuan->barangs ?? []) as $item)
            @if(trim($item->keterangan ?? '') !== '')
            <tr>
                <td align="center">{{ $loop->iteration }}</td>
                <td style="text-align:justify;">{{ $item->keterangan }}</td>
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
                    <li>Barang yang sudah diajukan / sudah ada, tidak boleh diajukan lagi (kecuali barang habis pakai & rusak).</li>
                    <li>Setiap permintaan penggantian barang (kecuali barang habis pakai) harus disertai dengan barang lama.</li>
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

    <p class="footer-note">
        Nb: harga sudah include pajak tapi belum include pajak proteksi, ready info Kadipiro, indent 3–5 hari dari PO dan transaksi
    </p>

    <!-- ===== LAMPIRAN (halaman baru) ===== -->
    @if ($lampiranAssets->count())
    <div class="page-break">
        <h2 align="center" style="margin:20px 0 10px;">LAMPIRAN PENGAJUAN BARANG INTERN</h2>
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