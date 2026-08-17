<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportDataController;
use App\Http\Controllers\GaleriController;
use App\Http\Controllers\InfaqSodaqohController;
use App\Http\Controllers\InventarisController;
use App\Http\Controllers\JadwalKegiatanController;
use App\Http\Controllers\JadwalPetugasController;
use App\Http\Controllers\JamaahController;
use App\Http\Controllers\KasMasjidController;
use App\Http\Controllers\KepengurusanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\ProfilMasjidController;
use App\Http\Controllers\PublicDetailController;
use App\Http\Controllers\PublicGaleriController;
use App\Http\Controllers\PublicJadwalController;
use App\Http\Controllers\PublicKeuanganController;
use App\Http\Controllers\PublicLandingController;
use App\Http\Controllers\PublicPengumumanController;
use App\Http\Controllers\PublicPengurusController;
use App\Http\Controllers\PublicPetugasSholatController;
use App\Http\Controllers\RelawanController;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\UmumController;
use App\Http\Controllers\UserManagementController;
use App\Models\Mosque;
use App\Support\SiteContext;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    // Lupa password
    Route::get('/lupa-password', [LoginController::class, 'forgotPassword'])->name('password.request');
    Route::post('/lupa-password', [LoginController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [LoginController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [LoginController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// ============================================================
// WEBSITE PUBLIK — bisa diakses tanpa login
// ============================================================
Route::group([], function () {
    // Pintu depan: pengunjung → website; pengurus yang sudah login → dashboard.
    Route::get('/', function () {
        return auth()->check()
            ? app(DashboardController::class)->index(request())
            : redirect()->route('public.beranda');
    })->name('dashboard');
    Route::get('/publik', [PublicLandingController::class, 'index'])->name('public.beranda');
    // Agenda digabung ke halaman Jadwal Kegiatan — redirect untuk link lama.
    Route::redirect('/publik/agenda', '/publik/jadwal-kegiatan', 301);
    Route::get('/publik/jadwal-kegiatan', [PublicJadwalController::class, 'index'])->name('public.jadwal');
    Route::get('/publik/pengumuman', [PublicPengumumanController::class, 'index'])->name('public.pengumuman');
    Route::get('/publik/tentang-masjid', function () {
        $mosque = Mosque::find(SiteContext::mosqueId());

        return view('pages.public.tentang-masjid', ['mosque' => $mosque]);
    })->name('public.tentang');
    Route::get('/publik/galeri', [PublicGaleriController::class, 'index'])->name('public.galeri');
    Route::get('/publik/galeri/{album}', [PublicGaleriController::class, 'album'])->name('public.galeri.album');
    Route::get('/publik/keuangan', [PublicKeuanganController::class, 'index'])->name('public.keuangan');
    Route::get('/publik/pengurus', [PublicPengurusController::class, 'index'])->name('public.pengurus');
    Route::get('/publik/jadwal-petugas', [PublicPetugasSholatController::class, 'index'])->name('public.petugas');
    Route::get('/publik/kegiatan/{id}', [PublicDetailController::class, 'kegiatan'])->name('public.kegiatan.detail');
    Route::get('/publik/pengumuman/{id}', [PublicDetailController::class, 'pengumuman'])->name('public.pengumuman.detail');
});

Route::middleware(['auth', 'active.user', 'menu.access', 'activity.log'])->group(function () {

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
    Route::delete('/keuangan/kas-masjid/{id}', [KasMasjidController::class, 'destroy'])->name('keuangan.kas.destroy');
    Route::get('/kegiatan/agenda', [AgendaController::class, 'index'])->name('kegiatan.agenda');
    Route::post('/kegiatan/agenda', [AgendaController::class, 'store'])->name('kegiatan.agenda.store');
    Route::put('/kegiatan/agenda/{kegiatan}', [AgendaController::class, 'update'])->name('kegiatan.agenda.update');
    Route::delete('/kegiatan/agenda/{kegiatan}', [AgendaController::class, 'destroy'])->name('kegiatan.agenda.destroy');
    Route::get('/kegiatan/jadwal-kegiatan', [JadwalKegiatanController::class, 'index'])->name('kegiatan.jadwal');
    Route::get('/kegiatan/jadwal-petugas-sholat', [JadwalPetugasController::class, 'index'])->name('kegiatan.petugas');
    Route::post('/kegiatan/jadwal-petugas-sholat', [JadwalPetugasController::class, 'store'])->name('kegiatan.petugas.store');
    Route::put('/kegiatan/jadwal-petugas-sholat/{id}', [JadwalPetugasController::class, 'update'])->name('kegiatan.petugas.update');
    Route::delete('/kegiatan/jadwal-petugas-sholat/{id}', [JadwalPetugasController::class, 'destroy'])->name('kegiatan.petugas.destroy');
    Route::get('/kegiatan/galeri', [GaleriController::class, 'index'])->name('kegiatan.galeri');
    Route::post('/kegiatan/galeri', [GaleriController::class, 'store'])->name('kegiatan.galeri.store');
    Route::put('/kegiatan/galeri/{albumId}', [GaleriController::class, 'update'])->name('kegiatan.galeri.update');
    Route::delete('/kegiatan/galeri/{albumId}', [GaleriController::class, 'destroy'])->name('kegiatan.galeri.destroy');
    Route::post('/kegiatan/galeri/{albumId}/photos', [GaleriController::class, 'uploadPhotos'])->name('kegiatan.galeri.photos.upload');
    Route::post('/kegiatan/galeri/{albumId}/photos/{mediaId}/cover', [GaleriController::class, 'setCoverPhoto'])->name('kegiatan.galeri.photos.cover');
    Route::delete('/kegiatan/galeri/{albumId}/photos/{mediaId}', [GaleriController::class, 'deletePhoto'])->name('kegiatan.galeri.photos.delete');
    Route::patch('/kegiatan/galeri/{albumId}/publish', [GaleriController::class, 'togglePublish'])->name('kegiatan.galeri.publish');
    Route::get('/pengumuman', [PengumumanController::class, 'index'])->name('pengumuman');
    Route::post('/pengumuman', [PengumumanController::class, 'store'])->name('pengumuman.store');
    Route::put('/pengumuman/{pengumuman}', [PengumumanController::class, 'update'])->name('pengumuman.update');
    Route::delete('/pengumuman/{pengumuman}', [PengumumanController::class, 'destroy'])->name('pengumuman.destroy');
    Route::get('/kepengurusan', [KepengurusanController::class, 'index'])->name('kepengurusan');
    Route::post('/kepengurusan/jabatan', [KepengurusanController::class, 'store'])->name('kepengurusan.jabatan.store');
    Route::post('/kepengurusan/jabatan/parent', [KepengurusanController::class, 'updateParent'])->name('kepengurusan.jabatan.parent');
    Route::post('/kepengurusan/penempatan', [KepengurusanController::class, 'updatePenempatan'])->name('kepengurusan.penempatan');
    Route::post('/kepengurusan/jabatan/rename', [KepengurusanController::class, 'renameJabatan'])->name('kepengurusan.jabatan.rename');
    Route::delete('/kepengurusan/jabatan', [KepengurusanController::class, 'destroyJabatan'])->name('kepengurusan.jabatan.destroy');
    Route::post('/kepengurusan/jabatan/reset', [KepengurusanController::class, 'resetJabatan'])->name('kepengurusan.jabatan.reset');
    Route::get('/relawan', [RelawanController::class, 'index'])->name('relawan');
    Route::post('/relawan/{kegiatan}', [RelawanController::class, 'updateRelawan'])->name('relawan.update');
    Route::get('/surat', [SuratController::class, 'index'])->name('surat');
    Route::post('/surat', [SuratController::class, 'store'])->name('surat.store');
    Route::put('/surat/{surat}', [SuratController::class, 'update'])->name('surat.update');
    Route::delete('/surat/{surat}', [SuratController::class, 'destroy'])->name('surat.destroy');
    Route::get('/inventaris', [InventarisController::class, 'index'])->name('inventaris');
    Route::post('/inventaris', [InventarisController::class, 'store'])->name('inventaris.store');
    Route::put('/inventaris/{inventari}', [InventarisController::class, 'update'])->name('inventaris.update');
    Route::delete('/inventaris/{inventari}', [InventarisController::class, 'destroy'])->name('inventaris.destroy');
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');
    Route::get('/laporan/ekspor/excel', [LaporanController::class, 'exportExcelAll'])->name('laporan.excel-all');
    Route::get('/laporan/ekspor/pdf', [LaporanController::class, 'exportPdfAll'])->name('laporan.pdf-all');
    Route::get('/laporan/{id}/print', [LaporanController::class, 'printReport'])->name('laporan.print');
    Route::get('/laporan/{id}/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.pdf');
    Route::get('/laporan/{id}/excel', [LaporanController::class, 'exportExcel'])->name('laporan.excel');
    Route::get('/pengaturan/profil-masjid', [ProfilMasjidController::class, 'index'])->name('pengaturan.profil');
    Route::post('/pengaturan/profil-masjid', [ProfilMasjidController::class, 'update'])->name('pengaturan.profil.update');
    Route::delete('/pengaturan/profil-masjid/logo', [ProfilMasjidController::class, 'destroyLogo'])->name('pengaturan.profil.logo.destroy');
    Route::get('/pengaturan/umum', [UmumController::class, 'index'])->name('pengaturan.umum');
    Route::post('/pengaturan/umum/profil-aplikasi', [UmumController::class, 'updateProfilAplikasi'])->name('pengaturan.umum.profil');
    Route::post('/pengaturan/umum/notifikasi', [UmumController::class, 'updateNotifikasi'])->name('pengaturan.umum.notifikasi');
    Route::post('/pengaturan/umum/jadwal-sholat', [UmumController::class, 'updateJadwalSholat'])->name('pengaturan.umum.sholat');
    Route::post('/pengaturan/umum/backup', [UmumController::class, 'updateBackup'])->name('pengaturan.umum.backup');
    Route::get('/pengaturan/umum/backup/ekspor', [UmumController::class, 'exportData'])->name('pengaturan.umum.backup.ekspor');
    Route::post('/pengaturan/umum/backup/impor', [UmumController::class, 'importData'])->name('pengaturan.umum.backup.impor');
    Route::post('/pengaturan/umum/password', [UmumController::class, 'updatePassword'])->name('pengaturan.umum.password');
    Route::post('/data-jamaah/impor', [JamaahController::class, 'import'])->name('jamaah.import');
    Route::get('/pengaturan/user-management', [UserManagementController::class, 'index'])->name('pengaturan.user-management');
    Route::post('/pengaturan/user-management', [UserManagementController::class, 'store'])->name('pengaturan.user-management.store');
    Route::put('/pengaturan/user-management/{id}', [UserManagementController::class, 'update'])->name('pengaturan.user-management.update');
    Route::delete('/pengaturan/user-management/{id}', [UserManagementController::class, 'destroy'])->name('pengaturan.user-management.destroy');
    Route::patch('/pengaturan/user-management/{id}/status', [UserManagementController::class, 'updateStatus'])->name('pengaturan.user-management.status');
    Route::post('/pengaturan/user-management/{id}/reset-password', [UserManagementController::class, 'resetPassword'])->name('pengaturan.user-management.reset');

    Route::get('/pengawasan/log-aktivitas', [ActivityLogController::class, 'index'])->name('activity-log');

    // Profil Saya
    Route::get('/profil', [ProfilController::class, 'index'])->name('profil');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::post('/profil/password', [ProfilController::class, 'updatePassword'])->name('profil.password');

    // Verifikasi email
    Route::get('/email/verify/{id}/{hash}', [ProfilController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/email/verification-notification', [ProfilController::class, 'resendVerification'])->name('verification.send');

    // Ekspor per modul (CSV)
    Route::get('/ekspor/pengumuman', [ExportDataController::class, 'pengumuman'])->name('ekspor.pengumuman');
    Route::get('/ekspor/surat', [ExportDataController::class, 'surat'])->name('ekspor.surat');
    Route::get('/ekspor/inventaris', [ExportDataController::class, 'inventaris'])->name('ekspor.inventaris');
    Route::get('/ekspor/agenda', [ExportDataController::class, 'agenda'])->name('ekspor.agenda');
    Route::get('/ekspor/jadwal-kegiatan', [ExportDataController::class, 'jadwal'])->name('ekspor.jadwal');
    Route::get('/ekspor/kepengurusan', [ExportDataController::class, 'kepengurusan'])->name('ekspor.kepengurusan');
    Route::get('/ekspor/relawan', [ExportDataController::class, 'relawan'])->name('ekspor.relawan');
    Route::get('/ekspor/jadwal-petugas', [ExportDataController::class, 'jadwalPetugas'])->name('ekspor.jadwal-petugas');
});
