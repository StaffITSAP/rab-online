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
            padding: 4px;
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

        .note,
        .footer-note {
            font-size: 9px;
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

        .item-table tbody tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
    </style>
</head>

<body>
    @php $showSignature = $showSignature ?? true; @endphp

    <h2 align="center" style="margin: 5px 0;">FORM PENGAJUAN BARANG INTERN</h2>
    <h3 align="center" style="margin: 5px 0;">
        No RAB : {{ strtoupper($pengajuan->no_rab ?? '') }}
    </h3>
    <h2 align="center" style="margin: 5px 0;">CV Solusi Arya Prima</h2>
    <h3 align="center" style="margin: 5px 0;">
        CABANG {{ strtoupper(optional($pengajuan->user?->userStatus?->cabang)->kode ?? '-') }}
    </h3>

    <table style="font-size: 9px;">
        <tr>
            <td rowspan="5" class="logo-cell">
                <img src="{{ public_path('logo-sap.png') }}" alt="Logo">
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
                <span style="float:left;">
                    Tanggal Berangkat/Realisasi :
                    {{ $pengajuan->tgl_realisasi ? \Carbon\Carbon::parse($pengajuan->tgl_realisasi)->translatedFormat('d F Y') : '-' }}
                </span>
                <span style="float:right;">
                    Jam Berangkat : {{ $pengajuan->jam ?? '-' }}
                </span>
                <div style="clear:both;"></div>

                <span style="float:left;">
                    Tanggal Pulang:
                    {{ $pengajuan->tgl_pulang ? \Carbon\Carbon::parse($pengajuan->tgl_pulang)->translatedFormat('d F Y') : '-' }}
                </span>
                <div style="clear:both;"></div>
            </td>
            <td>
                Jumlah Personil : {{ $pengajuan->jml_personil ?? 0 }}
                <br>
                Nama Personil : {{ $pengajuan->dinasPersonils->map(fn($p) => $p->nama_personil ?? optional($p->user)->name)->filter()->join(', ') }}
            </td>
        </tr>
        <tr>
            <td><strong>Nama Pemohon : {{ optional($pengajuan->user)->name ?? '-' }}</strong></td>
            <td>
                <strong>Total : Rp {{ number_format($pengajuan->total_biaya ?? 0, 0, ',', '.') }}</strong>
            </td>
        </tr>
    </table>

    <table class="item-table" style="font-size: 9px;">
        <thead>
            <tr>
                <th>NO</th>
                <th>Deskripsi</th>
                <th>Keterangan</th>
                <th>PIC</th>
                <th>Qty/Hari</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php
            $grouped = $pengajuan->dinas->groupBy('deskripsi');
            $no = 1;
            $grandTotal = 0;
            @endphp
            @foreach ($grouped as $deskripsi => $details)
            @php $rowspan = $details->count(); @endphp
            @foreach ($details as $index => $detail)
            <tr>
                @if ($index === 0)
                <td rowspan="{{ $rowspan }}">{{ $no++ }}</td>
                <td rowspan="{{ $rowspan }}"><strong>{{ strtoupper($deskripsi) }}</strong></td>
                @endif
                <td>{{ $detail->keterangan }}</td>
                <td style="text-align: center">{{ $detail->pic }}</td>
                <td style="text-align: center">{{ $detail->jml_hari }}</td>
                <td style="text-align: right"><span style="float: left;">Rp</span>{{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                <td style="text-align: right"><span style="float: left;">Rp</span>{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
            @php $grandTotal += $detail->subtotal; @endphp
            @endforeach
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" align="center"><strong>TOTAL</strong></td>
                <td align="right"><strong>Rp {{ number_format(collect($pengajuan->dinas ?? [])->sum('subtotal') ?? 0, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>
    <table border="1" cellspacing="0" cellpadding="2" width="100%" style="margin-top: 5px;">
        <thead>
            <tr style="text-align: center; font-weight: bold;">
                <td>No Activity</td>
                <td>Dinas</td>
                <td>KETERANGAN</td>
            </tr>
        </thead>
        <tbody>
            @foreach ($pengajuan->dinasActivities as $activity)
            <tr>
                <td>{{ $activity->no_activity }}</td>
                <td>{{ $activity->nama_dinas }}</td>
                <td>{{ $activity->keterangan }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p align="center" style="font-size: 12px;">
        {{ optional(optional(optional($pengajuan->user)->userStatus)->cabang)->nama ?? 'Cabang Tidak Diketahui' }}

        {{ $pengajuan->created_at ? \Carbon\Carbon::parse($pengajuan->created_at)->translatedFormat('d F Y') : '' }}
    </p>

    {{-- Tanda Tangan --}}
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
</body>

</html>