<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PengajuansExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse; // ⬅️ penting

class ExportPengajuansController extends Controller
{
    // Download semua data (tanpa filter)
    public function all(Request $request)
    {
        /** @var BinaryFileResponse $response */
        $response = Excel::download(new PengajuansExport(null), 'pengajuans.xlsx');

        $response->setPrivate();
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    // Download sesuai filter tabel saat ini
    public function filtered(Request $request)
    {
        $filters = $request->has('filters')
            ? json_decode($request->query('filters'), true)
            : null;

        /** @var BinaryFileResponse $response */
        $response = Excel::download(
            new PengajuansExport(is_array($filters) ? $filters : null),
            'pengajuans_filtered.xlsx'
        );

        $response->setPrivate();
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
