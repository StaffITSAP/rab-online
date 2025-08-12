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
        .section-table th, .section-table td { border: 1px solid #444; padding: 2px 4px; vertical-align: top; }
        .section-table th { background: #eee; font-size: 8px; }
        .section-table td { font-size: 8px; line-height: 1.1; }

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

        /* Style untuk halaman baru */
        .page-break { page-break-before: always; }
        
        /* Style untuk lampiran */
        .lampiran-container { margin-top: 20px; }
        .download-link { 
            color: #0066cc; 
            text-decoration: underline; 
            font-size: 8px; 
            display: inline-block; 
            margin-top: 2px; 
        }
        .lampiran-image { 
            max-height: 200px; 
            max-width: 100%; 
            object-fit: contain; 
            margin-top: 5px; 
            border: 1px solid #ccc; 
        }
        .file-info { 
            font-size: 8px; 
            color: #666; 
            margin-top: 2px; 
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

    // Data lampiran dinas
    $lampiranDinas = collect($pengajuan->lampiranDinas ?? []);

    // Function untuk cek apakah file adalah gambar
    function isImageFile($filePath) {
        $abs = public_path('storage/' . $filePath);
        if (!is_file($abs)) return false;
        $mimeType = mime_content_type($abs);
        return $mimeType && str_starts_with($mimeType, 'image/');
    }
@endphp

<h2 align="center" style="margin: 5px 0;">FORM RAB DINAS LUAR KOTA</h2>
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
            <span style="float:left;">
                Tanggal Berangkat/Realisasi :
                {{ $pengajuan->tgl_realisasi ? \Carbon\Carbon::parse($pengajuan->tgl_realisasi)->translatedFormat('d F Y') : '-' }}
            </span>
            <span style="float:right;">
                Jam : {{ $pengajuan->jam ?? '-' }}
            </span>
            <div style="clear:both;"></div>

            <span style="float:left;">
                Tanggal Pulang:
                {{ $pengajuan->tgl_pulang ? \Carbon\Carbon::parse($pengajuan->tgl_pulang)->translatedFormat('d F Y') : '-' }}
            </span>
            <div style="clear:both;"></div>
        </td>
        <td>
            Jumlah Personil : {{ $pengajuan->jml_personil ?? 0 }}<br>
            Nama Personil : {{ $pengajuan->dinasPersonils->map(fn($p) => $p->nama_personil ?? optional($p->user)->name)->filter()->join(', ') }}
        </td>
    </tr>
    <tr>
        <td>
            <strong>Nama Pemohon : {{ optional($pengajuan->user)->name ?? '-' }}</strong>
        </td>
        <td style="text-align: right">
            <strong><span style="float: left;">Total : </span> Rp {{ number_format($pengajuan->total_biaya ?? 0, 0, ',', '.') }}</strong>
        </td>
    </tr>
</table>

{{-- ====== TABEL DINAS ====== --}}
<table class="section-table item-table" style="font-size: 9px;">
    <thead>
        <tr>
            <th style="width:4%;">NO</th>
            <th style="width:15%;">DESKRIPSI</th>
            <th style="width:25%;">KETERANGAN</th>
            <th style="width:10%;">PIC</th>
            <th style="width:8%;">QTY/HARI</th>
            <th style="width:18%;">HARGA SATUAN</th>
            <th style="width:20%;">SUBTOTAL</th>
        </tr>
    </thead>
    <tbody>
        @php
        $grouped = $pengajuan->dinas->groupBy('deskripsi');
        $no = 1;
        $grandTotal = 0;
        @endphp
        @forelse ($grouped as $deskripsi => $details)
            @php $rowspan = $details->count(); @endphp
            @foreach ($details as $index => $detail)
                <tr>
                    @if ($index === 0)
                    <td rowspan="{{ $rowspan }}" class="text-center">{{ $no++ }}</td>
                    <td rowspan="{{ $rowspan }}" class="text-justify"><strong>{{ strtoupper($deskripsi) }}</strong></td>
                    @endif
                    <td class="text-justify">{{ $detail->keterangan ?? '-' }}</td>
                    <td class="text-center">{{ $detail->pic ?? '-' }}</td>
                    <td class="text-center">{{ $detail->jml_hari ?? 0 }}</td>
                    <td class="text-right"><span style="float: left;">Rp </span>{{ number_format((int)($detail->harga_satuan ?? 0), 0, ',', '.') }}</td>
                    <td class="text-right nowrap"><span style="float: left;">Rp </span> {{ number_format((int)($detail->subtotal ?? 0), 0, ',', '.') }}</td>
                </tr>
                @php $grandTotal += $detail->subtotal; @endphp
            @endforeach
        @empty
            <tr><td colspan="7" class="text-center">Belum ada data dinas.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6" class="text-center"><strong>TOTAL</strong></td>
            <td class="text-right"><strong><span style="float: left;">Rp </span>{{ number_format(collect($pengajuan->dinas ?? [])->sum('subtotal') ?? 0, 0, ',', '.') }}</strong></td>
        </tr>
    </tfoot>
</table>

{{-- ====== TABEL AKTIVITAS ====== --}}
@if($pengajuan->dinasActivities && $pengajuan->dinasActivities->count() > 0)
<table class="section-table" style="margin-top: 12px; font-size: 9px;">
    <thead>
        <tr>
            <th style="width:15%;">NO ACTIVITY</th>
            <th style="width:30%;">NAMA DINAS</th>
            <th style="width:55%;">KETERANGAN</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($pengajuan->dinasActivities as $activity)
        <tr>
            <td class="text-center">{{ $activity->no_activity ?? '-' }}</td>
            <td class="text-justify">{{ $activity->nama_dinas ?? '-' }}</td>
            <td class="text-justify">{{ $activity->keterangan ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- ====== KETERANGAN ====== --}}
@if(isset($pengajuan->keterangan) && trim($pengajuan->keterangan) !== '')
<div style="margin-top:12px; font-size:9px;">
    <strong>Keterangan:</strong><br>
    <div class="text-justify" style="margin-top:4px;">
        {{ $pengajuan->keterangan }}
    </div>
</div>
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

{{-- ====== LAMPIRAN DI HALAMAN BARU ====== --}}
@if ($lampiranDinas->count())
    <div class="page-break">
        <h2 align="center" style="margin: 20px 0 10px 0;">LAMPIRAN RAB DINAS LUAR KOTA</h2>
        <h3 align="center" style="margin: 5px 0 20px 0;">No RAB : {{ strtoupper($pengajuan->no_rab ?? '') }}</h3>
        
        <table class="section-table no-break lampiran-container">
            <thead>
                <tr>
                    <th style="width:5%;">NO</th>
                    <th style="width:35%;">NAMA LAMPIRAN</th>
                    <th style="width:60%;">FILE / PREVIEW</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($lampiranDinas as $lamp)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="text-justify">
                            <strong>{{ $lamp->original_name ?? '-' }}</strong>
                            <div class="file-info">
                                File: {{ basename($lamp->file_path ?? '') }}<br>
                                @php
                                    $filePath = $lamp->file_path ?? '';
                                    $abs = $filePath ? public_path('storage/' . $filePath) : null;
                                    $fileSize = $abs && is_file($abs) ? filesize($abs) : 0;
                                @endphp
                                @if ($fileSize > 0)
                                    Size: {{ number_format($fileSize / 1024, 1) }} KB
                                @endif
                            </div>
                        </td>
                        <td class="text-justify">
                            @php $filePath = $lamp->file_path ?? ''; @endphp
                            
                            @if ($filePath)
                                @if (isImageFile($filePath))
                                    {{-- Image File - Show preview --}}
                                    <div style="text-align: center;">
                                        <img src="{{ public_path('storage/' . $filePath) }}" alt="Lampiran" class="lampiran-image"><br>
                                        <small style="color: #666; margin-top: 5px; display: block;">{{ basename($filePath) }}</small>
                                    </div>
                                @else
                                    {{-- File lainnya (PDF, DOC, dll) - Show name only --}}
                                    <div style="text-align: center; padding: 20px; border: 2px dashed #ccc; background-color: #f9f9f9;">
                                        <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTE0IDJINlY0SDIwVjIySDZWMjBIMTRWMThINlYyMEgyMFYyMkg2VjRIMTRWMloiIGZpbGw9IiM4ODgiLz4KPC9zdmc+" alt="File" style="width: 40px; height: 40px;"><br>
                                        <strong style="color: #666;">{{ strtoupper(pathinfo($filePath, PATHINFO_EXTENSION)) }} FILE</strong><br>
                                        <small style="color: #666;">{{ basename($filePath) }}</small>
                                    </div>
                                @endif
                            @else
                                <div style="text-align: center; color: #999; padding: 20px;">
                                    File tidak tersedia
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        {{-- Footer untuk halaman lampiran --}}
        <div style="margin-top: 30px; text-align: center; color: #666; font-size: 8px;">
            <hr style="border: none; border-top: 1px solid #ccc; margin: 20px 0;">
            Halaman Lampiran - {{ $companyName }}<br>
            Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }}
        </div>
    </div>
@endif

</body>
</html>