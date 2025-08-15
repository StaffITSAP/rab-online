<?php

namespace App\Filament\Resources\PengajuanResource\Widgets;

use App\Models\Pengajuan;
use App\Models\PengajuanStatus;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat as StatsOverviewWidgetStat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengajuanStats extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $user = Auth::user();
        $role = $user->getRoleNames()->first();
        $targetUrl = url('/web/pengajuans');
        $semuaUrl = url('/web/semua-pengajuan');

        // Dapatkan semua id pengajuan yang dibuat OLEH user
        $createdIds = Pengajuan::where('user_id', $user->id)->pluck('id')->toArray();

        // Dapatkan semua id pengajuan yang HARUS DIA approve (muncul di pengajuan_statuses)
        $toApproveIds = PengajuanStatus::where('user_id', $user->id)
            ->pluck('pengajuan_id')
            ->toArray();

        // Gabungkan dan unique
        $allIds = array_unique(array_merge($createdIds, $toApproveIds));

        // ============ JUMLAH PENGAJUAN ============
        if ($role === 'superadmin') {
            // Hitung semua pengajuan aktif (tidak termasuk soft-deleted)
            $totalPengajuan = Pengajuan::count();
        } else {
            // Pengajuan yang dibuat user
            $buatSendiriIds = Pengajuan::where('user_id', $user->id)
                ->pluck('id')
                ->toArray();

            // Pengajuan yang dia approve (tidak duplikat)
            $approveIds = PengajuanStatus::where('user_id', $user->id)
                ->pluck('pengajuan_id')
                ->toArray();

            // Gabungkan & hilangkan duplikat
            $allIds = array_unique(array_merge($buatSendiriIds, $approveIds));

            // Pastikan hanya ambil yang BELUM di-soft-delete
            $totalPengajuan = Pengajuan::whereIn('id', $allIds)->count();
        }

        // ============ JUMLAH PENGAJUAN HARI INI ============
        if ($role === 'superadmin') {
            $pengajuanHariIni = Pengajuan::whereDate('created_at', Carbon::today())->count();
        } else {
            $pengajuanHariIni = Pengajuan::whereIn('id', $allIds)
                ->whereDate('created_at', Carbon::today())
                ->count();
        }

        // ============ TIPE RAB TERBANYAK ============
        $subQuery = Pengajuan::select(
            'id',
            'user_id',
            'no_rab',
            DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(no_rab, '/', 2), '/', -1) as kode")
        )->whereNull('deleted_at');

        if ($role !== 'superadmin') {
            $subQuery->whereIn('id', $allIds);
        }
        $subQuerySql = $subQuery->toSql();

        $topTipeRab = DB::table(DB::raw("({$subQuerySql}) as pengajuans"))
            ->mergeBindings($subQuery->getQuery())
            ->join('tipe_rabs', 'pengajuans.kode', '=', 'tipe_rabs.kode')
            ->select(
                'pengajuans.kode',
                'tipe_rabs.nama as tipe_rab',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('pengajuans.kode', 'tipe_rabs.nama')
            ->orderByDesc('total')
            ->limit(1)
            ->first();

        // ============ TOTAL PENGAJUAN (Rp) DENGAN STATUS "SELESAI" ATAU "EXPIRED" (UNLOCKED) ============
        if ($role === 'superadmin') {
            $totalBiaya = Pengajuan::where(function ($q) {
                $q->where('status', 'selesai')
                    ->orWhere(function ($q2) {
                        $q2->where('status', 'expired')
                            ->where('expired_unlocked', true);
                    });
            })
                ->sum('total_biaya');
        } else {
            $totalBiaya = Pengajuan::whereIn('id', $allIds)
                ->where(function ($q) {
                    $q->where('status', 'selesai')
                        ->orWhere(function ($q2) {
                            $q2->where('status', 'expired')
                                ->where('expired_unlocked', true);
                        });
                })
                ->sum('total_biaya');
        }


        return [
            StatsOverviewWidgetStat::make('Total Pengajuan', $totalPengajuan)
                ->description('Semua Pengajuan yang Anda buat & yang Anda setujui/minta persetujuan')
                ->color('info')
                ->url($semuaUrl)
                ->icon('heroicon-o-document-check'),

            StatsOverviewWidgetStat::make('Jumlah Pengajuan Hari Ini', $pengajuanHariIni)
                ->description('Pengajuan Status Menunggu (buat/minta approve) hari ini')
                ->color('warning')
                ->url($targetUrl)
                ->icon('heroicon-o-document-arrow-down'),

            StatsOverviewWidgetStat::make('Tipe RAB Terbanyak', $topTipeRab?->tipe_rab ?? 'Tidak ada data')
                ->description('Tipe RAB paling sering diajukan')
                ->color('info')
                ->url($targetUrl)
                ->icon('heroicon-o-document-text'),

            StatsOverviewWidgetStat::make('Total Pengajuan (Rp)', 'Rp ' . number_format($totalBiaya, 0, ',', '.'))
                ->description('Total biaya pengajuan berstatus selesai')
                ->color('success')
                ->url($semuaUrl)
                ->icon('heroicon-o-currency-dollar'),
        ];
    }
}
