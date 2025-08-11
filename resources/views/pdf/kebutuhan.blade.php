<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <style>
        body { font-family: sans-serif; font-size: 10px; margin:0; padding:0; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        td, th { border: 1px solid #000; padding: 4px 6px; font-size: 9px; }

        .ttd { text-align: center; vertical-align: top; height: 100px; font-size: 12px; }
        .ttd-table, .ttd-table td { border: none; }

        .section-table { width: 100%; margin-top: 12px; border: 1px solid #444; border-collapse: collapse; font-size: 9px; }
        .section-table th, .section-table td { border: 1px solid #444; padding: 4px 8px; }
        .section-table th { background: #eee; }

        .note-table { width: 100%; margin-top: 12px; border: 1px solid #444; border-collapse: collapse; font-size: 9px; }
        .note-table td { border: none; padding: 4px 8px; vertical-align: top; }
        .note-title { width: 60px; font-weight: bold; white-space: nowrap; }

        .footer-note { font-size: 9px; margin-top: 10px; }
        .no-break { page-break-inside: avoid; }

        .logo-cell { width: 8%; padding: 2px; }
        .logo-cell img { width: 100%; height: auto; }

        .item-table td, .item-table th { word-break: break-word; white-space: pre-line; }
        .item-table tbody tr { page-break-inside: avoid; page-break-after: auto; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .nowrap { white-space: nowrap; }

        /* Container 50% kiri untuk tabel amplop */
        .half-left {
            width: 50%;
            float: left;
            margin-top: 12px;
            box-sizing: border-box;
        }
        /* Clear float agar bagian setelahnya tidak ketimpa */
        .clear { clear: both; height: 0; line-height: 0; }
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

    // ====== DATA ======
    $itemsKebutuhan = collect($pengajuan->pengajuan_marcomm_kebutuhans ?? []);
    $itemsAmplop    = collect($pengajuan->marcommKebutuhkanAmplops ?? $pengajuan->marcommKebutuhanAmplops ?? []); // fallback nama relasi
    $lampiranDinas  = collect($pengajuan->lampiranDinas ?? []);

    $totalItems  = (int) $itemsKebutuhan->sum(fn($i) => (int)($i->subtotal ?? 0));
    $totalAmplop = (int) $itemsAmplop->sum(fn($r) => (int)($r->jumlah ?? 0));

    // Tampilkan amplop jika ada flag di kebutuhan atau ada data amplop
    $adaAmplop = $itemsKebutuhan->contains(fn($i) => (int)($i->kebutuhan_amplop ?? 0) === 1) || $itemsAmplop->isNotEmpty();
@endphp

<h2 align="center" style="margin: 5px 0;">FORM PENGAJUAN RAB MARCOMM KEBUTUHAN</h2>
<h3 align="center" style="margin: 5px 0;">No RAB : {{ strtoupper($pengajuan->no_rab ?? '') }}</h3>
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
        <td>Fax : -</td>
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
        <td style="text-align: right">
            <strong><span style="float: left;">Total :  </span> Rp {{ number_format($pengajuan->total_biaya ?? $totalItems, 0, ',', '.') }}</strong>
        </td>
    </tr>
</table>

{{-- ====== TABEL KEBUTUHAN ====== --}}
<table class="section-table item-table" style="font-size: 9px;">
    <thead>
        <tr>
            <th style="width:4%;">NO</th>
            <th style="width:36%;">DESKRIPSI KEBUTUHAN</th>
            <th style="width:8%;">QTY</th>
            <th style="width:12%;">TIPE</th>
            <th style="width:20%;">HARGA SATUAN</th>
            <th style="width:20%;">SUBTOTAL</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($itemsKebutuhan as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-justify">{{ $item->deskripsi ?? '-' }}</td>
                <td class="text-center">{{ $item->qty ?? 0 }}</td>
                <td class="text-center">{{ $item->tipe ?? '-' }}</td>
                <td class="text-right"><span style="float: left;">Rp </span>{{ number_format((int)($item->harga_satuan ?? 0), 0, ',', '.') }}</td>
                <td class="text-right nowrap"><span style="float: left;">Rp </span> {{ number_format((int)($item->subtotal ?? 0), 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center">Belum ada data kebutuhan.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" class="text-center"><strong>TOTAL</strong></td>
            <td class="text-right"><strong><span style="float: left;">Rp </span>{{ number_format($totalItems, 0, ',', '.') }}</strong></td>
        </tr>
    </tfoot>
</table>

{{-- ====== KETERANGAN (paragraf) ====== --}}
<div style="margin-top:12px; font-size:9px;">
    <strong>Keterangan:</strong><br>
    <div class="text-justify" style="margin-top:4px;">
        {{ trim($pengajuan->keterangan ?? '') !== '' ? $pengajuan->keterangan : '-' }}
    </div>
</div>

{{-- ====== KEBUTUHAN AMPLOP (kiri 50%) ====== --}}
@if ($adaAmplop && $itemsAmplop->count())
    <div class="half-left">
        <table class="section-table no-break" style="margin-top:0;">
            <thead>
                <tr><th colspan="3">FORM PENGAJUAN MARCOMM KEBUTUHAN AMPLOP</th></tr>
                <tr>
                    <th style="width:10%;">NO</th>
                    <th style="width:60%;">CABANG</th>
                    <th style="width:30%;">JUMLAH</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($itemsAmplop as $row)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="text-justify">{{ $row->cabang ?? '-' }}</td>
                        <td class="text-right">{{ number_format((int)($row->jumlah ?? 0), 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="text-center"><strong>JUMLAH AMPLOP</strong></td>
                    <td class="text-right"><strong>{{ number_format($totalAmplop, 0, ',', '.') }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="clear"></div>
@endif

{{-- ====== LAMPIRAN DINAS (opsional) ====== --}}
@if ($lampiranDinas->count())
    <table class="section-table no-break">
        <thead>
            <tr><th colspan="3">LAMPIRAN RAB PERJALANAN DINAS</th></tr>
            <tr>
                <th style="width:8%;">NO</th>
                <th style="width:62%;">NAMA LAMPIRAN</th>
                <th style="width:30%;">FILE</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lampiranDinas as $lamp)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-justify">{{ $lamp->original_name ?? '-' }}</td>
                    <td class="text-justify">
                        {{ $lamp->file_path ?? '-' }}
                        @php $abs = isset($lamp->file_path) ? public_path('storage/'.$lamp->file_path) : null; @endphp
                        @if ($abs && is_file($abs) && str_starts_with(mime_content_type($abs) ?: '', 'image/'))
                            <br><img src="{{ $abs }}" alt="Lampiran" style="max-height:140px; max-width:100%; object-fit:contain;">
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<p align="center" style="font-size: 12px;">
    {{ optional(optional($pengajuan->user)->userStatus)->kota ?? 'Kota Tidak Diketahui' }},
    {{ $pengajuan->created_at ? \Carbon\Carbon::parse($pengajuan->created_at)->translatedFormat('d F Y') : '' }}
</p>

{{-- ====== TANDA TANGAN ====== --}}
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
