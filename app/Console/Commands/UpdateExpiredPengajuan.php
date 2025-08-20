<?php

namespace App\Console\Commands;

use App\Models\Pengajuan;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateExpiredPengajuan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pengajuan:update-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status pengajuan menjadi expired jika sudah lewat 1 hari dari tanggal realisasi';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Mulai mengecek pengajuan yang expired...');

        // Ambil pengajuan dengan status 'menunggu' dan tgl_realisasi tidak null
        $pengajuans = Pengajuan::where('status', 'menunggu')
            ->whereNotNull('tgl_realisasi')
            ->get();

        $expiredCount = 0;
        $today = Carbon::now()->startOfDay();

        foreach ($pengajuans as $pengajuan) {
            $tglRealisasi = Carbon::parse($pengajuan->tgl_realisasi)->startOfDay();
            $batasWaktu = $tglRealisasi->copy()->addDays(1); // 1 hari setelah tanggal realisasi

            // Jika hari ini sudah melewati batas waktu (lebih dari 1 hari)
            if ($today->gt($batasWaktu)) {
                $pengajuan->update(['status' => 'expired']);
                $expiredCount++;

                $this->line("Pengajuan ID {$pengajuan->id} (No RAB: {$pengajuan->no_rab}) diubah menjadi expired");
            }
        }

        $this->info("Selesai. Total {$expiredCount} pengajuan diubah menjadi expired.");

        return Command::SUCCESS;
    }
}
