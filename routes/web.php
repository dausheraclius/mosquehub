<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JamaahController;
use App\Http\Controllers\KasMasjidController;
use App\Http\Controllers\InfaqSodaqohController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\JadwalKegiatanController;
use App\Http\Controllers\GaleriController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PengumumanController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {

    Route::view('/', 'pages.dashboard')->name('dashboard');
    Route::get('/data-jamaah', [JamaahController::class, 'index'])->name('jamaah.index');
    Route::post('/data-jamaah', [JamaahController::class, 'store'])->name('jamaah.store');
    Route::put('/data-jamaah/{id}', [JamaahController::class, 'update'])->name('jamaah.update');
    Route::delete('/data-jamaah/{id}', [JamaahController::class, 'destroy'])->name('jamaah.destroy');
    Route::get('/keuangan/infaq-sodaqoh', [InfaqSodaqohController::class, 'index'])->name('keuangan.infaq');
    Route::post('/keuangan/infaq-sodaqoh/donasi', [InfaqSodaqohController::class, 'storeDonasi'])->name('keuangan.infaq.donasi.store');
    Route::put('/keuangan/infaq-sodaqoh/donasi/{id}', [InfaqSodaqohController::class, 'updateDonasi'])->name('keuangan.infaq.donasi.update');
    Route::delete('/keuangan/infaq-sodaqoh/donasi/{id}', [InfaqSodaqohController::class, 'destroyDonasi'])->name('keuangan.infaq.donasi.destroy');
    Route::post('/keuangan/infaq-sodaqoh/peserta', [InfaqSodaqohController::class, 'storePeserta'])->name('keuangan.infaq.peserta.store');
    Route::put('/keuangan/infaq-sodaqoh/peserta/{id}', [InfaqSodaqohController::class, 'updatePeserta'])->name('keuangan.infaq.peserta.update');
    Route::delete('/keuangan/infaq-sodaqoh/peserta/{id}', [InfaqSodaqohController::class, 'destroyPeserta'])->name('keuangan.infaq.peserta.destroy');
    Route::post('/keuangan/infaq-sodaqoh/peserta/{id}/anggota', [InfaqSodaqohController::class, 'storeAnggota'])->name('keuangan.infaq.peserta.anggota.store');
    Route::delete('/keuangan/infaq-sodaqoh/anggota/{id}', [InfaqSodaqohController::class, 'destroyAnggota'])->name('keuangan.infaq.peserta.anggota.destroy');
    Route::post('/keuangan/infaq-sodaqoh/setoran', [InfaqSodaqohController::class, 'storeSetoran'])->name('keuangan.infaq.setoran.store');
    Route::put('/keuangan/infaq-sodaqoh/setoran/{id}', [InfaqSodaqohController::class, 'updateSetoran'])->name('keuangan.infaq.setoran.update');
    Route::delete('/keuangan/infaq-sodaqoh/setoran/{id}', [InfaqSodaqohController::class, 'destroySetoran'])->name('keuangan.infaq.setoran.destroy');
    Route::get('/csrf-token', fn () => response()->json(['token' => csrf_token()]));
    Route::get('/keuangan/kas-masjid', [KasMasjidController::class, 'index'])->name('keuangan.kas');
    Route::post('/keuangan/kas-masjid', [KasMasjidController::class, 'store'])->name('keuangan.kas.store');
    Route::put('/keuangan/kas-masjid/{id}', [KasMasjidController::class, 'update'])->name('keuangan.kas.update');
    Route::get('/kegiatan/agenda', [AgendaController::class, 'index'])->name('kegiatan.agenda');
    Route::post('/kegiatan/agenda', [AgendaController::class, 'store'])->name('kegiatan.agenda.store');
    Route::put('/kegiatan/agenda/{kegiatan}', [AgendaController::class, 'update'])->name('kegiatan.agenda.update');
    Route::delete('/kegiatan/agenda/{kegiatan}', [AgendaController::class, 'destroy'])->name('kegiatan.agenda.destroy');
    Route::get('/kegiatan/jadwal-kegiatan', [JadwalKegiatanController::class, 'index'])->name('kegiatan.jadwal');
    Route::get('/kegiatan/galeri', [GaleriController::class, 'index'])->name('kegiatan.galeri');
    Route::post('/kegiatan/galeri', [GaleriController::class, 'store'])->name('kegiatan.galeri.store');
    Route::put('/kegiatan/galeri/{album}', [GaleriController::class, 'update'])->name('kegiatan.galeri.update');
    Route::delete('/kegiatan/galeri/{album}', [GaleriController::class, 'destroy'])->name('kegiatan.galeri.destroy');
    Route::post('/kegiatan/galeri/{album}/photos', [GaleriController::class, 'uploadPhotos'])->name('kegiatan.galeri.photos.upload');
    Route::post('/kegiatan/galeri/{album}/photos/{mediaId}/cover', [GaleriController::class, 'setCoverPhoto'])->name('kegiatan.galeri.photos.cover');
    Route::delete('/kegiatan/galeri/{album}/photos/{mediaId}', [GaleriController::class, 'deletePhoto'])->name('kegiatan.galeri.photos.delete');
    Route::patch('/kegiatan/galeri/{album}/publish', [GaleriController::class, 'togglePublish'])->name('kegiatan.galeri.publish');
    Route::get('/pengumuman', [PengumumanController::class, 'index'])->name('pengumuman');
    Route::post('/pengumuman', [PengumumanController::class, 'store'])->name('pengumuman.store');
    Route::put('/pengumuman/{pengumuman}', [PengumumanController::class, 'update'])->name('pengumuman.update');
    Route::delete('/pengumuman/{pengumuman}', [PengumumanController::class, 'destroy'])->name('pengumuman.destroy');
    Route::view('/kepengurusan', 'pages.kepengurusan')->name('kepengurusan');
    Route::view('/relawan', 'pages.relawan')->name('relawan');
    Route::view('/surat', 'pages.surat')->name('surat');
    Route::view('/inventaris', 'pages.inventaris')->name('inventaris');
    Route::view('/laporan', 'pages.laporan')->name('laporan');
    Route::view('/pengaturan/profil-masjid', 'pages.pengaturan.profil-masjid')->name('pengaturan.profil');
    Route::view('/pengaturan/umum', 'pages.pengaturan.umum')->name('pengaturan.umum');
    Route::view('/pengaturan/user-management', 'pages.pengaturan.user-management')->name('pengaturan.user-management');

});