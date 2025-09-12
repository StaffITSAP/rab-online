<?php

use App\Http\Controllers\CetakPengajuanServiceController;
use Illuminate\Support\Facades\Route;
use App\Models\Pengajuan;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\ExportPengajuansController;
use App\Http\Controllers\ExportPenggunaanMobilController;
use App\Http\Controllers\ExportPenggunaanTeknisiController;
use App\Models\PengajuanBiayaService;

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

Route::get('/exports/pengajuans/all', [ExportPengajuansController::class, 'all'])
    ->name('exports.pengajuans.all');

Route::get('/exports/pengajuans/filtered', [ExportPengajuansController::class, 'filtered'])
    ->name('exports.pengajuans.filtered');

Route::get('/exports/penggunaan-mobil/all', [ExportPenggunaanMobilController::class, 'all'])
    ->name('exports.penggunaan_mobil.all');

Route::get('/exports/penggunaan-mobil/filtered', [ExportPenggunaanMobilController::class, 'filtered'])
    ->name('exports.penggunaan_mobil.filtered');

Route::get('/exports/penggunaan-teknisi/all', [ExportPenggunaanTeknisiController::class, 'all'])
    ->name('exports.penggunaan_teknisi.all');

Route::get('/exports/penggunaan-teknisi/filtered', [ExportPenggunaanTeknisiController::class, 'filtered'])
    ->name('exports.penggunaan_teknisi.filtered');
// Routes untuk export services
Route::get('/exports/services/all', [App\Http\Controllers\ExportServiceController::class, 'exportAll'])
    ->name('exports.services.all')
    ->middleware('auth');

Route::match(['get', 'post'], '/exports/services/filtered', [App\Http\Controllers\ExportServiceController::class, 'exportFiltered'])
    ->name('exports.services.filtered')
    ->middleware('auth');

// PDF Preview
Route::get('/pengajuan-biaya-service/{pengajuan}/pdf', function (PengajuanBiayaService $pengajuan) {
    $view = request()->query('tipe') === 'internal'
        ? 'pdf.service_internal'
        : 'pdf.service_pelanggan';

    $pdf = Pdf::loadView($view, compact('pengajuan'))
        ->setPaper('a4', 'portrait');

    $filename = "SERVICE_" . str_replace(['/', '\\'], '_', $pengajuan->id);

    return $pdf->stream("{$filename}.pdf");
})->name('pengajuan_biaya_service.pdf.preview')->middleware('auth');

// PDF Download
Route::get('/pengajuan-biaya-service/{pengajuan}/download-pdf', function (PengajuanBiayaService $pengajuan) {
    $view = request()->query('tipe') === 'internal'
        ? 'pdf.service_internal'
        : 'pdf.service_pelanggan';

    $pdf = Pdf::loadView($view, compact('pengajuan'))
        ->setPaper('a4', 'portrait');

    $filename = "SERVICE_" . str_replace(['/', '\\'], '_', $pengajuan->id);

    return $pdf->download("{$filename}.pdf");
})->name('pengajuan_biaya_service.pdf.download')->middleware('auth');
// Exports untuk Pengajuan Biaya Service
Route::get('/exports/pengajuan-biaya-service/all', [CetakPengajuanServiceController::class, 'all'])
    ->name('exports.pengajuan_biaya_service.all')
    ->middleware('auth');

Route::match(['get', 'post'], '/exports/pengajuan-biaya-service/filtered', [CetakPengajuanServiceController::class, 'filtered'])
    ->name('exports.pengajuan_biaya_service.filtered')
    ->middleware('auth');
