<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicDetailController;
use App\Http\Controllers\PublicGaleriController;
use App\Http\Controllers\PublicJadwalController;
use App\Http\Controllers\PublicKeuanganController;
use App\Http\Controllers\PublicLandingController;
use App\Http\Controllers\PublicPengumumanController;
use App\Http\Controllers\PublicPengurusController;
use App\Http\Controllers\PublicPetugasSholatController;
use App\Models\Mosque;
use App\Support\SiteContext;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? app(DashboardController::class)->index(request())
        : redirect()->route('public.beranda');
})->name('dashboard');

Route::get('/publik', [PublicLandingController::class, 'index'])->name('public.beranda');
Route::redirect('/publik/agenda', '/publik/jadwal-kegiatan', 301);
Route::get('/publik/jadwal-kegiatan', [PublicJadwalController::class, 'index'])->name('public.jadwal');
Route::get('/publik/pengumuman', [PublicPengumumanController::class, 'index'])->name('public.pengumuman');
Route::get('/publik/tentang-masjid', function () {
    return view('pages.public.tentang-masjid', ['mosque' => Mosque::find(SiteContext::mosqueId())]);
})->name('public.tentang');
Route::get('/publik/galeri', [PublicGaleriController::class, 'index'])->name('public.galeri');
Route::get('/publik/galeri/{album}', [PublicGaleriController::class, 'album'])->name('public.galeri.album');
Route::get('/publik/keuangan', [PublicKeuanganController::class, 'index'])->name('public.keuangan');
Route::get('/publik/pengurus', [PublicPengurusController::class, 'index'])->name('public.pengurus');
Route::get('/publik/jadwal-petugas', [PublicPetugasSholatController::class, 'index'])->name('public.petugas');
Route::get('/publik/kegiatan/{id}', [PublicDetailController::class, 'kegiatan'])->name('public.kegiatan.detail');
Route::get('/publik/pengumuman/{id}', [PublicDetailController::class, 'pengumuman'])->name('public.pengumuman.detail');
