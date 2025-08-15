<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PengajuansExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportPenggunaanTeknisiController extends Controller
{
    // Download semua data (khusus yang menggunakan_teknisi = 1)
    public function all(Request $request)
    {
        /** @var BinaryFileResponse $response */
        $response = Excel::download(new PengajuansExport(null, false, true), 'penggunaan_teknisi.xlsx');

        $response->setPrivate();
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    // Download sesuai filter aktif di tabel Penggunaan Teknisi
    public function filtered(Request $request)
    {
        $filters = $request->has('filters')
            ? json_decode($request->query('filters'), true)
            : null;

        /** @var BinaryFileResponse $response */
        $response = Excel::download(
            new PengajuansExport(is_array($filters) ? $filters : null, false, true),
            'penggunaan_teknisi_filtered.xlsx'
        );

        $response->setPrivate();
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
