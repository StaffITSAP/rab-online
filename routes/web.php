<?php

use Illuminate\Support\Facades\Route;
use App\Models\Pengajuan;
use Barryvdh\DomPDF\Facade\Pdf;

Route::get('/', function () {
    return redirect('/web');
});
Route::get('/pengajuan/{pengajuan}/pdf', function (Pengajuan $pengajuan) {
    $view = match ((int) $pengajuan->tipe_rab_id) {
        1 => 'pdf.pengajuan',   // Barang Intern
        2 => 'pdf.dinas',       // Perjalanan Dinas
        3 => 'pdf.kegiatan',       // Marcomm Event/Kegiatan
        4 => 'pdf.promosi',     // Marcomm Promosi
        5 => 'pdf.kebutuhan',     // Marcomm Kebutuhan
        default => abort(404, 'Template PDF tidak tersedia untuk tipe ini.'),
    };

    // Tentukan orientasi
    $orientation = match ((int) $pengajuan->tipe_rab_id) {
        3 => 'landscape', // kegiatan
        4 => 'landscape', // promosi
        5 => 'landscape', // kebutuhan
        default => 'portrait', // pengajuan & dinas
    };

    $pdf = Pdf::loadView($view, compact('pengajuan'))
        ->setPaper('a4', $orientation);

    $filename = str_replace(['/', '\\'], '_', $pengajuan->no_rab);

    return $pdf->stream("RAB_{$filename}.pdf");
})->name('pengajuan.pdf.preview');

Route::get('/pengajuan/{pengajuan}/download-pdf', function (Pengajuan $pengajuan) {
    $view = match ((int) $pengajuan->tipe_rab_id) {
        1 => 'pdf.pengajuan',
        2 => 'pdf.dinas',
        3 => 'pdf.kegiatan',
        4 => 'pdf.promosi',
        5 => 'pdf.kebutuhan',
        default => abort(404, 'Template PDF tidak tersedia untuk tipe ini.'),
    };

    $orientation = match ((int) $pengajuan->tipe_rab_id) {
        3 => 'landscape',
        4 => 'landscape',
        5 => 'landscape',
        default => 'portrait',
    };

    $pdf = Pdf::loadView($view, compact('pengajuan'))
        ->setPaper('a4', $orientation);

    $filename = str_replace(['/', '\\'], '_', $pengajuan->no_rab);

    return $pdf->download("RAB_{$filename}.pdf");
})->name('pengajuan.pdf.download');
