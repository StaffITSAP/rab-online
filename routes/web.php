<?php

use Illuminate\Support\Facades\Route;
use App\Models\Pengajuan;
use Barryvdh\DomPDF\Facade\Pdf;

Route::get('/', function () {
    return redirect('/web');
});
Route::get('/pengajuan/{pengajuan}/pdf', function (Pengajuan $pengajuan) {
    $view = match ((int) $pengajuan->tipe_rab_id) {
        1 => 'pdf.pengajuan',
        2 => 'pdf.dinas',
        default => abort(404, 'Template PDF tidak tersedia untuk tipe ini.'),
    };

    $pdf = Pdf::loadView($view, compact('pengajuan'));
    $filename = str_replace(['/', '\\'], '_', $pengajuan->no_rab);

    return $pdf->stream("RAB_{$filename}.pdf");
})->name('pengajuan.pdf.preview');

Route::get('/pengajuan/{pengajuan}/download-pdf', function (Pengajuan $pengajuan) {
    $view = match ((int) $pengajuan->tipe_rab_id) {
        1 => 'pdf.pengajuan',
        2 => 'pdf.dinas',
        default => abort(404, 'Template PDF tidak tersedia untuk tipe ini.'),
    };

    $pdf = Pdf::loadView($view, compact('pengajuan'));
    $filename = str_replace(['/', '\\'], '_', $pengajuan->no_rab);

    return $pdf->download("RAB_{$filename}.pdf");
})->name('pengajuan.pdf.download');
