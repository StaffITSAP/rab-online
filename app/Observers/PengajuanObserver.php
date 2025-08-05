<?php

namespace App\Observers;

use App\Models\Pengajuan;
use Carbon\Carbon;

class PengajuanObserver
{
    /**
     * Handle the Pengajuan "retrieved" event.
     * Dipanggil setiap kali model Pengajuan diambil dari database
     */
    public function retrieved(Pengajuan $pengajuan): void
    {
        // Hanya cek jika status masih 'menunggu' dan ada tgl_realisasi
        if ($pengajuan->status === 'menunggu' && $pengajuan->tgl_realisasi) {
            $this->checkAndUpdateExpired($pengajuan);
        }
    }

    /**
     * Handle the Pengajuan "saving" event.
     * Dipanggil sebelum model disave
     */
    public function saving(Pengajuan $pengajuan): void
    {
        // Cek expired saat akan menyimpan data
        if ($pengajuan->status === 'menunggu' && $pengajuan->tgl_realisasi) {
            $this->checkAndUpdateExpired($pengajuan);
        }
    }

    /**
     * Check dan update status expired
     */
    private function checkAndUpdateExpired(Pengajuan $pengajuan): void
    {
        $tglRealisasi = Carbon::parse($pengajuan->tgl_realisasi)->startOfDay();
        $today = Carbon::now()->startOfDay();
        $batasWaktu = $tglRealisasi->copy()->addDays(2);

        // Jika sudah melewati batas waktu (lebih dari 2 hari)
        if ($today->gt($batasWaktu)) {
            // Gunakan updateQuietly agar tidak trigger observer lagi
            $pengajuan->updateQuietly(['status' => 'expired']);
        }
    }
}
