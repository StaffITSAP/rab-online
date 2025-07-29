<?php

use Illuminate\Support\Facades\Route;
use App\Models\Pengajuan;
use Barryvdh\DomPDF\Facade\Pdf;

Route::get('/', function () {
    return redirect('/web');
});
Route::get('/pengajuan/{pengajuan}/pdf', function (Pengajuan $pengajuan) {
    $pdf = Pdf::loadView('pdf.pengajuan', compact('pengajuan'));
    $filename = str_replace(['/', '\\'], '_', $pengajuan->no_rab);
    return $pdf->stream("RAB_{$filename}.pdf");
})->name('pengajuan.pdf.preview');

Route::get('/pengajuan/{pengajuan}/download-pdf', function (Pengajuan $pengajuan) {
    $pdf = Pdf::loadView('pdf.pengajuan', compact('pengajuan'));
    $filename = str_replace(['/', '\\'], '_', $pengajuan->no_rab);
    return $pdf->download("RAB_{$filename}.pdf"); // <--- ubah dari ->stream() menjadi ->download()
})->name('pengajuan.pdf.download');

