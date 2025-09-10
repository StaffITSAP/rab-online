<?php

namespace App\Filament\Resources\PengajuanResource\Widgets;

use App\Models\Pengajuan;
use App\Models\PengajuanStatus;
use App\Models\Service;
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
        $user      = Auth::user();
        $role      = $user->getRoleNames()->first();
        $targetUrl = url('/web/pengajuans');
        $semuaUrl  = url('/web/semua-pengajuan');
        $serviceUrl = url('/web/services');

        // ---- id pengajuan yang relevan untuk user ini
        $createdIds   = Pengajuan::where('user_id', $user->id)->pluck('id')->toArray();
        $toApproveIds = PengajuanStatus::where('user_id', $user->id)->pluck('pengajuan_id')->toArray();
        $allIds       = array_unique(array_merge($createdIds, $toApproveIds));

        // ---- total pengajuan
        if ($role === 'superadmin') {
            $totalPengajuan = Pengajuan::count();
        } else {
            $totalPengajuan = Pengajuan::whereIn('id', $allIds)->count();
        }

        // ---- pengajuan hari ini
        if ($role === 'superadmin') {
            $pengajuanHariIni = Pengajuan::whereDate('created_at', Carbon::today())->count();
        } else {
            $pengajuanHariIni = Pengajuan::whereIn('id', $allIds)
                ->whereDate('created_at', Carbon::today())
                ->count();
        }

        // ---- tipe RAB terbanyak
        $subQuery = Pengajuan::select(
            'id',
            'user_id',
            'no_rab',
            DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(no_rab, '/', 2), '/', -1) as kode")
        )->whereNull('deleted_at');

        if ($role !== 'superadmin') {
            $subQuery->whereIn('id', $allIds);
        }
        $subSql = $subQuery->toSql();

        $topTipeRab = DB::table(DB::raw("({$subSql}) as pengajuans"))
            ->mergeBindings($subQuery->getQuery())
            ->join('tipe_rabs', 'pengajuans.kode', '=', 'tipe_rabs.kode')
            ->select('pengajuans.kode', 'tipe_rabs.nama as tipe_rab', DB::raw('COUNT(*) as total'))
            ->groupBy('pengajuans.kode', 'tipe_rabs.nama')
            ->orderByDesc('total')
            ->limit(1)
            ->first();

        // ---- total biaya selesai / expired unlocked
        if ($role === 'superadmin') {
            $totalBiaya = Pengajuan::where(function ($q) {
                $q->where('status', 'selesai')
                    ->orWhere(function ($q2) {
                        $q2->where('status', 'expired')->where('expired_unlocked', true);
                    });
            })->sum('total_biaya');
        } else {
            $totalBiaya = Pengajuan::whereIn('id', $allIds)
                ->where(function ($q) {
                    $q->where('status', 'selesai')
                        ->orWhere(function ($q2) {
                            $q2->where('status', 'expired')->where('expired_unlocked', true);
                        });
                })
                ->sum('total_biaya');
        }

        // ================== STATS SERVICE (fix) ==================
        $privilegedRoles = ['superadmin', 'manager', 'servis'];
        $canSeeAllServices = in_array($role, $privilegedRoles, true);

        $serviceBase = Service::query();
        if (! $canSeeAllServices) {
            // user biasa → hanya service yang dia buat sendiri
            $serviceBase->where('user_id', $user->id);
        }

        $totalService   = (clone $serviceBase)->count();
        $serviceRequest = (clone $serviceBase)->where('staging', 'request')->count();

        $descTotal   = $canSeeAllServices ? 'Semua service di sistem' : 'Service yang Anda ajukan';
        $descRequest = $canSeeAllServices ? 'Semua service berstatus Request' : 'Service Anda berstatus Request';

        // =========================================================

        $stats = [
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

        // Tambah 2 kartu service untuk SEMUA role (dengan query yang sudah disesuaikan di atas)
        $stats[] = StatsOverviewWidgetStat::make('Total Service', $totalService)
            ->description($descTotal)
            ->color('primary')
            ->url($serviceUrl)
            ->icon('heroicon-o-wrench-screwdriver');

        $stats[] = StatsOverviewWidgetStat::make('Service Request', $serviceRequest)
            ->description($descRequest)
            ->color('warning')
            ->url($serviceUrl . '?tableFilters[staging][value]=request')
            ->icon('heroicon-o-clock');

        return $stats;
    }
}
