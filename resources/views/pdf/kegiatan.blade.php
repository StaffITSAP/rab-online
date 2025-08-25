<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <style>
    /* dasar (match template 1) */
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

    .footer-note {
      font-size: 9px;
      margin-top: 10px;
    }

    .no-break {
      page-break-inside: avoid;
    }

    .page-break {
      page-break-before: always;
    }

    /* match template 1: hindari pre-line */
    .item-table td,
    .item-table th {
      word-break: break-word;
      white-space: normal;
    }

    /* Multi-column list (untuk Tim Pusat / Tim Cabang) */
    .multi-column-container {
      width: 100%;
      margin-top: 8px;
      overflow: hidden;
    }

    .column-item {
      float: left;
      box-sizing: border-box;
      padding-right: 2px;
    }

    .column-item:last-child {
      padding-right: 0;
    }

    .column-item table {
      margin-top: 0 !important;
    }

    .clear {
      clear: both;
      height: 0;
      line-height: 0;
    }

    .col-width-1 {
      width: 100%;
    }

    .col-width-2 {
      width: 49.5%;
    }

    .col-width-3 {
      width: 32.8%;
    }

    .col-width-4 {
      width: 24.5%;
    }

    .col-width-5 {
      width: 19.5%;
    }

    .col-width-6 {
      width: 16.2%;
    }

    /* Lampiran */
    .lampiran-container {
      margin-top: 20px;
    }

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

    /* ===== Watermark untuk status expired (merah) ===== */
    .wm-expired {
      position: fixed;
      top: 45%;
      left: 50%;
      transform: translate(-50%, -50%) rotate(-25deg);
      font-size: 120px;
      font-weight: 800;
      color: rgba(220, 0, 0, 0.18);
      /* MERAH transparan di layar */
      letter-spacing: 8px;
      z-index: 9999;
      pointer-events: none;
      /* tidak ganggu klik */
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
      /* kuning transparan (#ffc107) */
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
      /* merah lebih tegas */
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
        /* merah sedikit lebih pekat */
      }

      .wm-menunggu {
        color: rgba(255, 193, 7, 0.35);
      }

      .wm-ditolak {
        color: rgba(200, 0, 0, 0.35);
      }
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

  /* ====== DATA (KEGIATAN) ====== */
  $itemsKegiatan = collect($pengajuan->marcommKegiatans ?? []);
  $timPusatRows = collect($pengajuan->marcommKegiatanPusats ?? []);
  $timCabangRows = collect($pengajuan->marcommKegiatanCabangs ?? []);
  $lampiranKegiatan= collect($pengajuan->lampiranKegiatan ?? []);

  $totalKegiatan = (int) $itemsKegiatan->sum(fn($i) => (int)($i->subtotal ?? 0));

  /* ====== GROUPING UNTUK ROWSPAN (SEPERTI CONTOH) ====== */
  // Grup berdasarkan 'deskripsi' agar kolom NO & DESKRIPSI bisa rowspan per grup
  $kegiatanGrouped = $itemsKegiatan->groupBy(function ($row) {
  return strtoupper(trim($row->deskripsi ?? '-'));
  });

  /* ====== MULTI-COLUMN CHUNKING TIM ====== */
  $maxRowsPerColumn = 8;

  $pusatChunks = $timPusatRows->isNotEmpty() ? $timPusatRows->chunk($maxRowsPerColumn) : collect([]);
  $cabangChunks = $timCabangRows->isNotEmpty() ? $timCabangRows->chunk($maxRowsPerColumn) : collect([]);

  $totalColumns = $pusatChunks->count() + $cabangChunks->count();
  if ($totalColumns <= 1) $columnClass='col-width-1' ;
    elseif ($totalColumns==2) $columnClass='col-width-2' ;
    elseif ($totalColumns==3) $columnClass='col-width-3' ;
    elseif ($totalColumns==4) $columnClass='col-width-4' ;
    elseif ($totalColumns==5) $columnClass='col-width-5' ;
    else $columnClass='col-width-6' ;

    function isImageFile($filePath) {
    $abs=public_path('storage/' . $filePath);
    if (!is_file($abs)) return false;
    $mimeType=mime_content_type($abs);
    return $mimeType && str_starts_with($mimeType, 'image/' );
    }
    @endphp

    {{-- ===== Watermark sesuai status ===== --}}
    @php $status=strtolower($pengajuan->status ?? ''); @endphp

    @if ($status === 'expired')
    <div class="wm-expired">EXPIRED</div>
    @elseif ($status === 'menunggu')
    <div class="wm-menunggu">MENUNGGU</div>
    @elseif ($status === 'ditolak')
    <div class="wm-ditolak">DITOLAK</div>
    @endif

    <h2 align="center" style="margin:5px 0;">FORM PENGAJUAN RAB MARCOMM KEGIATAN PUSAT/SALES</h2>
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
          <span style="float:left;">
            Tanggal Berangkat/Realisasi :
            {{ $pengajuan->tgl_realisasi ? \Carbon\Carbon::parse($pengajuan->tgl_realisasi)->translatedFormat('d F Y') : '-' }}
          </span>
          <span style="float:right;">Jam : {{ $pengajuan->jam ?? '-' }}</span>
          <div style="clear:both;"></div>
          Tanggal Dibuat :
          {{ $pengajuan->created_at ? \Carbon\Carbon::parse($pengajuan->created_at)->translatedFormat('d F Y') : '' }}
        </td>
        <td>
          <span style="float:left;">
            <strong>Nama Pemohon : {{ optional($pengajuan->user)->name ?? '-' }}</strong>
          </span>
          <span style="float:right;"></span>
          <div style="clear:both;"></div>
          Jumlah Peserta : {{ $pengajuan->jml_personil ?? '-' }}
        </td>
      </tr>
      <tr>
        <td style="text-align:right;"><strong><span style="float:left;">Lokasi : </span> {{ $pengajuan->lokasi ?? '-' }}</strong></td>
        <td style="text-align:right;">
          <strong><span style="float:left;">Total : </span> Rp {{ number_format($pengajuan->total_biaya ?? $totalKegiatan, 0, ',', '.') }}</strong>
        </td>
      </tr>
    </table>

    {{-- ====== TABEL RAB KEGIATAN (ROWSPAN PER DESKRIPSI) ====== --}}
    <table class="section-table item-table" style="font-size:9px;">
      <thead>
        <tr>
          <th style="width:4%;">NO</th>
          <th style="width:20%;">DESKRIPSI</th>
          <th style="width:30%;">KETERANGAN</th>
          <th style="width:8%;">PIC</th>
          <th style="width:10%;">QTY/HARI</th>
          <th style="width:14%;">HARGA SATUAN</th>
          <th style="width:14%;">SUBTOTAL</th>
        </tr>
      </thead>
      <tbody>
        @php $noKelompok = 1; @endphp

        @forelse ($kegiatanGrouped as $deskripsi => $rows)
        @php $rowspan = $rows->count(); @endphp
        @foreach ($rows as $idx => $row)
        <tr>
          {{-- NO dan DESKRIPSI di-merge per grup (rowspan) --}}
          @if ($idx === 0)
          <td align="center" rowspan="{{ $rowspan }}">{{ $noKelompok }}</td>
          <td rowspan="{{ $rowspan }}" style="text-transform:uppercase; font-weight:bold;">
            {{ $deskripsi }}
          </td>
          @endif

          {{-- Detail baris per item --}}
          <td style="text-align:justify;">{{ $row->keterangan ?? '-' }}</td>
          <td align="center">{{ (int)($row->pic ?? 0) }}</td>
          <td align="center">{{ (int)($row->jml_hari ?? 0) }}</td>
          <td style="padding:4px 6px; text-align:right; vertical-align:top; white-space:nowrap;">
            <span style="float:left;">Rp</span>{{ number_format((int)($row->harga_satuan ?? 0), 0, ',', '.') }}
          </td>
          <td style="padding:4px 6px; text-align:right; vertical-align:top; white-space:nowrap;">
            <span style="float:left;">Rp</span>{{ number_format((int)($row->subtotal ?? 0), 0, ',', '.') }}
          </td>
        </tr>
        @endforeach
        @php $noKelompok++; @endphp
        @empty
        <tr>
          <td colspan="7" align="center">Belum ada data kegiatan.</td>
        </tr>
        @endforelse
      </tbody>
      <tfoot>
        <tr>
          <td colspan="6" align="center"><strong>TOTAL</strong></td>
          <td style="padding:4px 6px; text-align:right; vertical-align:top; white-space:nowrap;">
            <span style="float:left;"><strong>Rp</strong></span>
            <strong>{{ number_format($totalKegiatan, 0, ',', '.') }}</strong>
          </td>
        </tr>
      </tfoot>
    </table>

    {{-- ====== KETERANGAN ====== --}}
    <div style="margin-top:12px; font-size:9px;">
      <strong>Keterangan:</strong><br>
      <div class="text-justify" style="margin-top:4px;">
        {{ trim($pengajuan->keterangan ?? '') !== '' ? $pengajuan->keterangan : '-' }}
      </div>
    </div>

    {{-- ====== MULTI-COLUMN CONTAINER (TIM PUSAT / TIM CABANG) ====== --}}
    @if ($totalColumns > 0)
    <div class="multi-column-container">
      {{-- TIM PUSAT --}}
      @foreach ($pusatChunks as $chunkIndex => $chunk)
      <div class="column-item {{ $columnClass }}">
        <table class="section-table no-break" style="margin-top:0; font-size:8px;">
          <thead>
            <tr>
              <th colspan="3" style="font-size:7px; padding:1px 2px;">TIM PUSAT {{ $pusatChunks->count() > 1 ? '(' . ($chunkIndex + 1) . ')' : '' }}</th>
            </tr>
            <tr>
              <th style="width:8%;">NO</th>
              <th style="width:62%;">NAMA</th>
              <th style="width:30%;">GENDER</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($chunk as $row)
            <tr>
              <td align="center">{{ ($chunkIndex * $maxRowsPerColumn) + $loop->iteration }}</td>
              <td style="text-align:justify;">{{ $row->nama ?? '-' }}</td>
              <td style="text-align:center;">{{ $row->gender ?? '-' }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endforeach

      {{-- TIM CABANG (dengan Gender) --}}
      @foreach ($cabangChunks as $chunkIndex => $chunk)
      <div class="column-item {{ $columnClass }}">
        <table class="section-table no-break" style="margin-top:0; font-size:8px;">
          <thead>
            <tr>
              <th colspan="4" style="font-size:7px; padding:1px 2px;">TIM CABANG {{ $cabangChunks->count() > 1 ? '(' . ($chunkIndex + 1) . ')' : '' }}</th>
            </tr>
            <tr>
              <th style="width:8%;">NO</th>
              <th style="width:52%;">CABANG</th>
              <th style="width:24%;">NAMA</th>
              <th style="width:16%;">GENDER</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($chunk as $row)
            <tr>
              <td align="center">{{ ($chunkIndex * $maxRowsPerColumn) + $loop->iteration }}</td>
              <td style="text-align:justify;">{{ $row->cabang ?? '-' }}</td>
              <td style="text-align:justify;">{{ $row->nama ?? '-' }}</td>
              <td style="text-align:center;">{{ $row->gender ?? '-' }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endforeach

      <div class="clear"></div>
    </div>
    @endif

    <p align="center" style="font-size:12px;">
      {{ optional(optional($pengajuan->user)->userStatus)->kota ?? 'Kota Tidak Diketahui' }},
      {{ $pengajuan->created_at ? \Carbon\Carbon::parse($pengajuan->created_at)->translatedFormat('d F Y') : '' }}
    </p>

    {{-- ====== TANDA TANGAN ====== --}}
    <table class="ttd-table no-break" style="margin-top:10px;">
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

    {{-- ====== LAMPIRAN DI HALAMAN BARU (KEGIATAN) ====== --}}
    @if ($lampiranKegiatan->count())
    <div class="page-break">
      <h2 align="center" style="margin:20px 0 10px 0;">LAMPIRAN RAB MARCOMM KEGIATAN PUSAT/SALES</h2>
      <h3 align="center" style="margin:5px 0 20px 0;">No RAB : {{ strtoupper($pengajuan->no_rab ?? '') }}</h3>

      <table class="section-table no-break lampiran-container">
        <thead>
          <tr>
            <th style="width:5%;">NO</th>
            <th style="width:35%;">NAMA LAMPIRAN</th>
            <th style="width:60%;">FILE / PREVIEW</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($lampiranKegiatan as $lamp)
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
              <div style="text-align:center;">
                <img src="{{ public_path('storage/' . $filePath) }}" alt="Lampiran" class="lampiran-image"><br>
                <small style="color:#666; margin-top:5px; display:block;">{{ basename($filePath) }}</small>
              </div>
              @else
              <div style="text-align:center; padding:20px; border:2px dashed #ccc; background:#f9f9f9;">
                <strong style="color:#666;">{{ strtoupper(pathinfo($filePath, PATHINFO_EXTENSION)) }} FILE</strong><br>
                <small style="color:#666;">{{ basename($filePath) }}</small>
              </div>
              @endif
              @else
              <div style="text-align:center; color:#999; padding:20px;">File tidak tersedia</div>
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