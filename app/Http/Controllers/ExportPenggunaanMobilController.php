<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PengajuansExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportPenggunaanMobilController extends Controller
{
    // Download semua (dengan scope onlyMobil)
    public function all(Request $request)
    {
        /** @var BinaryFileResponse $response */
        $response = Excel::download(new PengajuansExport(null, true), 'penggunaan_mobil.xlsx');

        $response->setPrivate();
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    // Download sesuai filter tabel saat ini (use_car/use_pengiriman/status, dst)
    public function filtered(Request $request)
    {
        $filters = $request->has('filters')
            ? json_decode($request->query('filters'), true)
            : null;

        /** @var BinaryFileResponse $response */
        $response = Excel::download(
            new PengajuansExport(is_array($filters) ? $filters : null, true), // true = onlyMobil
            'penggunaan_mobil_filtered.xlsx'
        );

        $response->setPrivate();
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
