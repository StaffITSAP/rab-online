<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
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

        .keterangan-table {
            width: 100%;
            margin-top: 12px;
            border: 1px solid #444;
            border-collapse: collapse;
            font-size: 9px;
        }

        .keterangan-table th,
        .keterangan-table td {
            border: 1px solid #444;
            padding: 4px 8px;
        }

        .keterangan-table th {
            background: #eee;
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
            padding: 4px 8px 4px 8px;
            vertical-align: top;
        }

        .note-table .note-title {
            width: 60px;
            font-weight: bold;
            white-space: nowrap;
        }

        .footer-note {
            font-size: 9px;
            margin-top: 10px;
        }

        .no-break {
            page-break-inside: avoid;
        }

        .logo-cell {
            width: 8%;
            padding: 2px;
        }

        .logo-cell img {
            width: 100%;
            height: auto;
        }

        .item-table {
            page-break-inside: auto;
        }

        .item-table td,
        .item-table th {
            word-break: break-word;
            white-space: pre-line;
        }

        .item-table tbody tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
    </style>
</head>

<body>
    @php $showSignature = $showSignature ?? true; @endphp
    @php
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
    @endphp

    <h2 align="center" style="margin: 5px 0;">FORM PENGAJUAN BARANG INTERN</h2>
    <h3 align="center" style="margin: 5px 0;">
        No RAB : {{ strtoupper($pengajuan->no_rab ?? '') }}
    </h3>
    <h2 align="center" style="margin: 5px 0;">{{ $companyName }}</h2>
    <h3 align="center" style="margin: 5px 0;">
        CABANG {{ strtoupper(optional($pengajuan->user?->userStatus?->cabang)->kode ?? '-') }}
    </h3>

    <table style="font-size: 9px;">
        <tr>
            <td rowspan="5" class="logo-cell">
                <img src="{{ $logoPath }}" alt="Logo" style="height: 60px; width: 100px; margin-bottom:10px;">
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
            <td>Fax : - </td>
            <td>Cellular : {{ optional($pengajuan->user)->no_hp ?? '-' }}</td>
        </tr>
        <tr>
            <td>
                Tanggal Dibuat :
                {{ $pengajuan->created_at ? \Carbon\Carbon::parse($pengajuan->created_at)->translatedFormat('d F Y') : '' }}
            </td>
            <td>
                <strong>Nama Pemohon : {{ optional($pengajuan->user)->name ?? '-' }}</strong>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <strong>Total : Rp {{ number_format($pengajuan->total_biaya ?? 0, 0, ',', '.') }}</strong>
            </td>
        </tr>
    </table>

    <!-- Tabel Barang -->
    <table class="keterangan-table" style="font-size: 9px;">
        <thead>
            <tr>
                <th style="width:5%;">NO</th>
                <th style="width:30%;">NAMA DAN TIPE BARANG</th>
                <th style="width:30%;">KEPERLUAN</th>
                <th style="width:5%;">QTY</th>
                <th style="width:10%;">SATUAN</th>
                <th style="width:15%;">HARGA SATUAN</th>
                <th style="width:15%;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach (($pengajuan->barangs ?? []) as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->nama_barang ?? '' }}</td>
                <td>{{ $item->keperluan ?? '' }}</td>
                <td align="center">{{ $item->jumlah ?? '' }}</td>
                <td align="center">{{ $item->tipe_barang ?? '' }}</td>
                <td align="right">Rp {{ number_format($item->harga_unit ?? 0, 0, ',', '.') }}</td>
                <td align="right">Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" align="center"><strong>TOTAL</strong></td>
                <td align="right"><strong>Rp {{ number_format(collect($pengajuan->barangs ?? [])->sum('subtotal') ?? 0, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>
    <!-- END Tabel Barang -->

    <!-- TABEL KETERANGAN (BARU) -->
    <table class="keterangan-table">
        <thead>
            <tr>
                <th style="width:30px;">No</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach (($pengajuan->barangs ?? []) as $item)
            <tr>
                <td align="center">{{ $loop->iteration }}</td>
                <td style="text-align: justify;">
                    {{ $item->keterangan ?? '-' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <!-- END TABEL KETERANGAN -->

    <!-- NOTE TETAP DI BAWAH -->
    <table class="note-table">
        <tr>
            <td class="note-title">Note:</td>
            <td>
                <ul style="margin:0;padding-left:18px;">
                    <li>Barang yang sudah diajukan / sudah ada, tidak boleh diajukan lagi (kecuali barang habis pakai & rusak).</li>
                    <li>Setiap permintaan penggantian barang (kecuali barang habis pakai) harus disertai dengan barang lama.</li>
                </ul>
            </td>
        </tr>
    </table>

    <p align="center" style="font-size: 12px;">
        {{ optional(optional($pengajuan->user)->userStatus)->kota ?? 'Kota Tidak Diketahui' }},
        {{ $pengajuan->created_at ? \Carbon\Carbon::parse($pengajuan->created_at)->translatedFormat('d F Y') : '' }}
    </p>

    <!-- Tanda Tangan -->
    <table class="ttd-table no-break" style="margin-top: 10px;">
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
        Nb: harga sudah include pajak tapi belum include pajak proteksi, ready info Kadipiro, indent 3-5 hari dari PO dan transaksi
    </p>
</body>

</html>