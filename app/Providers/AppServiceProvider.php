<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Pengajuan;
use App\Models\Service;
use App\Observers\PengajuanObserver;
use App\Observers\ServiceObserver;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Daftarkan observer untuk auto-check expired pengajuan
        Pengajuan::observe(PengajuanObserver::class);
        Service::observe(ServiceObserver::class);
    }
}
