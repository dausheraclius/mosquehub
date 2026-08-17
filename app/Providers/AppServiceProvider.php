<?php

namespace App\Providers;

use App\Models\Mosque;
use App\Models\PengaturanUmum;
use App\Support\SiteContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        // Aplikasi berbahasa Indonesia — pastikan nama bulan/tanggal dari
        // Carbon (mis. translatedFormat) tampil dalam Bahasa Indonesia.
        Carbon::setLocale('id');

        // Data masjid dibagikan ke semua view (header, login, dashboard, halaman
        // publik, dsb.) supaya nama masjid & info identitas mengikuti perubahan
        // dari menu Pengaturan → Profil Masjid.
        View::composer('*', function ($view) {
            // Pakai mosque_id user yang login; halaman publik pakai masjid pertama.
            $mosqueId = SiteContext::mosqueId();

            $siteMosque = Mosque::firstOrCreate(
                ['id' => $mosqueId],
                ['name' => 'Masjid Al-Firdaus', 'status' => 'aktif']
            );
            $view->with('siteMosque', $siteMosque);

            // Pengaturan aplikasi (nama instansi, timezone, dll.) — dipakai
            // mis. di header sebagai cadangan kalau nama masjid kosong.
            $view->with('appPengaturan', PengaturanUmum::where('mosque_id', $mosqueId)->first());
        });
    }
}
