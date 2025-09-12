<?php

namespace App\Http\Controllers;

use App\Models\PengajuanBiayaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;


class CetakPengajuanServiceController extends Controller
{
    public function all()
    {
        $data = PengajuanBiayaService::with(['service', 'item'])->get();
        $pdf = Pdf::loadView('exports.services.all', compact('data'))->setPaper('a4', 'landscape');
        return $pdf->download('pengajuan-biaya-service-all.pdf');
    }

    public function filtered(Request $request)
    {
        $query = PengajuanBiayaService::with(['service', 'item']);

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->filled('pph_persen')) {
            $query->where('pph_persen', $request->pph_persen);
        }

        $data = $query->get();
        $pdf = Pdf::loadView('exports.services.filtered', compact('data'))->setPaper('a4', 'landscape');
        return $pdf->download('pengajuan-biaya-service-filtered.pdf');
    }
}
