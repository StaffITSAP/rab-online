<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\UpdateExpiredPengajuan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwalkan command untuk update expired pengajuan setiap hari jam 00:01
Schedule::command('pengajuan:update-expired')
    ->dailyAt('00:01')
    ->withoutOverlapping()
    ->runInBackground();

// Alternatif: Jalankan setiap jam jika ingin lebih sering
// Schedule::command('pengajuan:update-expired')->hourly();

// Atau menggunakan closure jika tidak ingin membuat command terpisah
Schedule::call(function () {
    $pengajuans = \App\Models\Pengajuan::where('status', 'menunggu')
        ->whereNotNull('tgl_realisasi')
        ->get();

    $today = \Carbon\Carbon::now()->startOfDay();

    foreach ($pengajuans as $pengajuan) {
        $tglRealisasi = \Carbon\Carbon::parse($pengajuan->tgl_realisasi)->startOfDay();
        $batasWaktu = $tglRealisasi->copy()->addDays(1);

        if ($today->gt($batasWaktu)) {
            $pengajuan->update(['status' => 'expired']);
        }
    }
})->dailyAt('00:01')->name('update-expired-pengajuan');
