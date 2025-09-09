<?php

namespace App\Http\Controllers;

use App\Exports\ServicesAllExport;
use App\Exports\ServicesFilteredExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class ExportServiceController extends Controller
{
    public function exportAll()
    {
        $filename = 'services-all-' . now()->format('Y-m-d-H-i-s') . '.xlsx';
        return Excel::download(new ServicesAllExport, $filename);
    }

    public function exportFiltered(Request $request)
    {
        // Bisa dari GET atau POST
        $filtersRaw = $request->input('filters', []);
        $search     = (string) $request->input('search', '');

        // Jika berupa string JSON -> decode
        if (is_string($filtersRaw)) {
            $decoded = json_decode($filtersRaw, true);
            $filters = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        } else {
            $filters = is_array($filtersRaw) ? $filtersRaw : [];
        }

        Log::info('Export Filtered Request:', [
            'filters' => $filters,
            'search'  => $search,
        ]);

        $filename = 'services-filtered-' . now()->format('Y-m-d-H-i-s') . '.xlsx';
        return Excel::download(new ServicesFilteredExport($filters, $search), $filename);
    }
}
