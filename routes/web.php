<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DonasiController;
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
use App\Http\Controllers\QurbanPesertaController;
use App\Http\Controllers\QurbanSetoranController;
use App\Http\Controllers\RelawanController;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\UmumController;
use App\Http\Controllers\UserManagementController;
use App\Models\Mosque;
use App\Support\SiteContext;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\MosqueController as SuperAdminMosqueController;
use App\Http\Controllers\SuperAdmin\UserController as SuperAdminUserController;

/*
|--------------------------------------------------------------------------
| Root Redirect
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard', ['locale' => session('locale') ?? 'id'])
        : redirect()->route('public.beranda');
});

/*
|--------------------------------------------------------------------------
| Public Routes (no locale prefix — handled by public.php)
|--------------------------------------------------------------------------
*/
require __DIR__.'/public.php';

/*
|--------------------------------------------------------------------------
| Auth Routes (no locale prefix — language toggled via session)
|--------------------------------------------------------------------------
*/
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/lupa-password', [LoginController::class, 'forgotPassword'])->name('password.request');
    Route::post('/lupa-password', [LoginController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [LoginController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [LoginController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::post('/set-locale', function (\Illuminate\Http\Request $request) {
    $locale = $request->input('locale', 'id');
    if (in_array($locale, ['id', 'en'], true)) {
        session(['locale' => $locale]);
        \Illuminate\Support\Facades\App::setLocale($locale);
    }
    return redirect()->route('login');
})->name('set-locale');

/*
|--------------------------------------------------------------------------
| Locale-scoped Routes
|--------------------------------------------------------------------------
*/
Route::prefix('{locale}')->whereIn('locale', ['id', 'en'])->group(function () {

    Route::post('/logout', [LoginController::class, 'destroy'])
        ->middleware(['locale', 'auth'])
        ->name('logout.locale');

    Route::middleware(['locale', 'auth', 'super.admin'])->prefix('super-admin')->name('super.')->group(function () {
    Route::get('/', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/masjid', [SuperAdminMosqueController::class, 'index'])->name('mosques');
    Route::get('/masjid/{mosque}', [SuperAdminMosqueController::class, 'show'])->name('mosques.show');
    Route::patch('/masjid/{mosque}/status', [SuperAdminMosqueController::class, 'updateStatus'])->name('mosques.status');
    Route::post('/masjid/{mosque}/switch', [SuperAdminMosqueController::class, 'switchTo'])->name('mosques.switch');
    Route::get('/users', [SuperAdminUserController::class, 'index'])->name('users');
    Route::post('/users/{user}/impersonate', [SuperAdminUserController::class, 'impersonate'])->name('users.impersonate');
    });

    Route::post('/stop-impersonate', [SuperAdminUserController::class, 'stopImpersonate'])
    ->middleware(['locale', 'auth'])
    ->name('stop-impersonate');

    /*
    |--------------------------------------------------------------
    | Authenticated Admin Routes
    |--------------------------------------------------------------
    */
    Route::middleware(['locale', 'auth', 'active.user', 'menu.access', 'activity.log'])->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/csrf-token', fn () => response()->json(['token' => csrf_token()]));

        Route::prefix('data-jamaah')->group(function () {
            Route::get('/', [JamaahController::class, 'index'])->name('jamaah.index');
            Route::post('/', [JamaahController::class, 'store'])->name('jamaah.store');
            Route::put('/{id}', [JamaahController::class, 'update'])->name('jamaah.update');
            Route::delete('/{id}', [JamaahController::class, 'destroy'])->name('jamaah.destroy');
            Route::post('/impor', [JamaahController::class, 'import'])->name('jamaah.import');
        });

        Route::prefix('keuangan/infaq-sodaqoh')->group(function () {
            Route::get('/', [InfaqSodaqohController::class, 'index'])->name('keuangan.infaq');
            Route::post('/donasi', [DonasiController::class, 'storeDonasi'])->name('keuangan.infaq.donasi.store');
            Route::put('/donasi/{id}', [DonasiController::class, 'updateDonasi'])->name('keuangan.infaq.donasi.update');
            Route::delete('/donasi/{id}', [DonasiController::class, 'destroyDonasi'])->name('keuangan.infaq.donasi.destroy');
            Route::post('/peserta', [QurbanPesertaController::class, 'storePeserta'])->name('keuangan.infaq.peserta.store');
            Route::put('/peserta/{id}', [QurbanPesertaController::class, 'updatePeserta'])->name('keuangan.infaq.peserta.update');
            Route::delete('/peserta/{id}', [QurbanPesertaController::class, 'destroyPeserta'])->name('keuangan.infaq.peserta.destroy');
            Route::post('/peserta/{id}/anggota', [QurbanPesertaController::class, 'storeAnggota'])->name('keuangan.infaq.peserta.anggota.store');
            Route::delete('/anggota/{id}', [QurbanPesertaController::class, 'destroyAnggota'])->name('keuangan.infaq.peserta.anggota.destroy');
            Route::post('/setoran', [QurbanSetoranController::class, 'storeSetoran'])->name('keuangan.infaq.setoran.store');
            Route::put('/setoran/{id}', [QurbanSetoranController::class, 'updateSetoran'])->name('keuangan.infaq.setoran.update');
            Route::delete('/setoran/{id}', [QurbanSetoranController::class, 'destroySetoran'])->name('keuangan.infaq.setoran.destroy');
        });

        Route::prefix('keuangan/kas-masjid')->group(function () {
            Route::get('/', [KasMasjidController::class, 'index'])->name('keuangan.kas');
            Route::post('/', [KasMasjidController::class, 'store'])->name('keuangan.kas.store');
            Route::put('/{id}', [KasMasjidController::class, 'update'])->name('keuangan.kas.update');
            Route::delete('/{id}', [KasMasjidController::class, 'destroy'])->name('keuangan.kas.destroy');
        });

        Route::prefix('kegiatan')->group(function () {
            Route::get('/agenda', [AgendaController::class, 'index'])->name('kegiatan.agenda');
            Route::post('/agenda', [AgendaController::class, 'store'])->name('kegiatan.agenda.store');
            Route::put('/agenda/{kegiatan}', [AgendaController::class, 'update'])->name('kegiatan.agenda.update');
            Route::delete('/agenda/{kegiatan}', [AgendaController::class, 'destroy'])->name('kegiatan.agenda.destroy');

            Route::get('/jadwal-kegiatan', [JadwalKegiatanController::class, 'index'])->name('kegiatan.jadwal');

            Route::get('/jadwal-petugas-sholat', [JadwalPetugasController::class, 'index'])->name('kegiatan.petugas');
            Route::post('/jadwal-petugas-sholat', [JadwalPetugasController::class, 'store'])->name('kegiatan.petugas.store');
            Route::put('/jadwal-petugas-sholat/{id}', [JadwalPetugasController::class, 'update'])->name('kegiatan.petugas.update');
            Route::delete('/jadwal-petugas-sholat/{id}', [JadwalPetugasController::class, 'destroy'])->name('kegiatan.petugas.destroy');

            Route::get('/galeri', [GaleriController::class, 'index'])->name('kegiatan.galeri');
            Route::post('/galeri', [GaleriController::class, 'store'])->name('kegiatan.galeri.store');
            Route::put('/galeri/{albumId}', [GaleriController::class, 'update'])->name('kegiatan.galeri.update');
            Route::delete('/galeri/{albumId}', [GaleriController::class, 'destroy'])->name('kegiatan.galeri.destroy');
            Route::post('/galeri/{albumId}/photos', [GaleriController::class, 'uploadPhotos'])->name('kegiatan.galeri.photos.upload');
            Route::post('/galeri/{albumId}/photos/{mediaId}/cover', [GaleriController::class, 'setCoverPhoto'])->name('kegiatan.galeri.photos.cover');
            Route::delete('/galeri/{albumId}/photos/{mediaId}', [GaleriController::class, 'deletePhoto'])->name('kegiatan.galeri.photos.delete');
            Route::patch('/galeri/{albumId}/publish', [GaleriController::class, 'togglePublish'])->name('kegiatan.galeri.publish');
        });

        Route::prefix('pengumuman')->group(function () {
            Route::get('/', [PengumumanController::class, 'index'])->name('pengumuman');
            Route::post('/', [PengumumanController::class, 'store'])->name('pengumuman.store');
            Route::put('/{pengumuman}', [PengumumanController::class, 'update'])->name('pengumuman.update');
            Route::delete('/{pengumuman}', [PengumumanController::class, 'destroy'])->name('pengumuman.destroy');
        });

        Route::prefix('kepengurusan')->group(function () {
            Route::get('/', [KepengurusanController::class, 'index'])->name('kepengurusan');
            Route::post('/jabatan', [KepengurusanController::class, 'store'])->name('kepengurusan.jabatan.store');
            Route::post('/jabatan/parent', [KepengurusanController::class, 'updateParent'])->name('kepengurusan.jabatan.parent');
            Route::post('/penempatan', [KepengurusanController::class, 'updatePenempatan'])->name('kepengurusan.penempatan');
            Route::post('/jabatan/rename', [KepengurusanController::class, 'renameJabatan'])->name('kepengurusan.jabatan.rename');
            Route::post('/jabatan/positions', [KepengurusanController::class, 'updatePositions'])->name('kepengurusan.jabatan.positions');
            Route::delete('/jabatan', [KepengurusanController::class, 'destroyJabatan'])->name('kepengurusan.jabatan.destroy');
            Route::post('/jabatan/reset', [KepengurusanController::class, 'resetJabatan'])->name('kepengurusan.jabatan.reset');
        });

        Route::get('/relawan', [RelawanController::class, 'index'])->name('relawan');
        Route::post('/relawan/{kegiatan}', [RelawanController::class, 'updateRelawan'])->name('relawan.update');

        Route::prefix('surat')->group(function () {
            Route::get('/', [SuratController::class, 'index'])->name('surat');
            Route::post('/', [SuratController::class, 'store'])->name('surat.store');
            Route::put('/{surat}', [SuratController::class, 'update'])->name('surat.update');
            Route::delete('/{surat}', [SuratController::class, 'destroy'])->name('surat.destroy');
        });

        Route::prefix('inventaris')->group(function () {
            Route::get('/', [InventarisController::class, 'index'])->name('inventaris');
            Route::post('/', [InventarisController::class, 'store'])->name('inventaris.store');
            Route::put('/{inventari}', [InventarisController::class, 'update'])->name('inventaris.update');
            Route::delete('/{inventari}', [InventarisController::class, 'destroy'])->name('inventaris.destroy');
        });

        Route::prefix('laporan')->group(function () {
            Route::get('/', [LaporanController::class, 'index'])->name('laporan');
            Route::get('/ekspor/excel', [LaporanController::class, 'exportExcelAll'])->name('laporan.excel-all');
            Route::get('/ekspor/pdf', [LaporanController::class, 'exportPdfAll'])->name('laporan.pdf-all');
            Route::get('/{id}/print', [LaporanController::class, 'printReport'])->name('laporan.print');
            Route::get('/{id}/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.pdf');
            Route::get('/{id}/excel', [LaporanController::class, 'exportExcel'])->name('laporan.excel');
        });

        Route::prefix('pengaturan')->group(function () {
            Route::get('/profil-masjid', [ProfilMasjidController::class, 'index'])->name('pengaturan.profil');
            Route::post('/profil-masjid', [ProfilMasjidController::class, 'update'])->name('pengaturan.profil.update');
            Route::delete('/profil-masjid/logo', [ProfilMasjidController::class, 'destroyLogo'])->name('pengaturan.profil.logo.destroy');

            Route::get('/umum', [UmumController::class, 'index'])->name('pengaturan.umum');
            Route::post('/umum/profil-aplikasi', [UmumController::class, 'updateProfilAplikasi'])->name('pengaturan.umum.profil');
            Route::post('/umum/notifikasi', [UmumController::class, 'updateNotifikasi'])->name('pengaturan.umum.notifikasi');
            Route::post('/umum/jadwal-sholat', [UmumController::class, 'updateJadwalSholat'])->name('pengaturan.umum.sholat');
            Route::post('/umum/backup', [UmumController::class, 'updateBackup'])->name('pengaturan.umum.backup');
            Route::get('/umum/backup/ekspor', [UmumController::class, 'exportData'])->name('pengaturan.umum.backup.ekspor');
            Route::post('/umum/backup/impor', [UmumController::class, 'importData'])->name('pengaturan.umum.backup.impor');
            Route::post('/umum/password', [UmumController::class, 'updatePassword'])->name('pengaturan.umum.password');

            Route::get('/user-management', [UserManagementController::class, 'index'])->name('pengaturan.user-management');
            Route::post('/user-management', [UserManagementController::class, 'store'])->name('pengaturan.user-management.store');
            Route::put('/user-management/{id}', [UserManagementController::class, 'update'])->name('pengaturan.user-management.update');
            Route::delete('/user-management/{id}', [UserManagementController::class, 'destroy'])->name('pengaturan.user-management.destroy');
            Route::patch('/user-management/{id}/status', [UserManagementController::class, 'updateStatus'])->name('pengaturan.user-management.status');
            Route::post('/user-management/{id}/reset-password', [UserManagementController::class, 'resetPassword'])->name('pengaturan.user-management.reset');
        });

        Route::get('/pengawasan/log-aktivitas', [ActivityLogController::class, 'index'])->name('activity-log');

        Route::prefix('profil')->group(function () {
            Route::get('/', [ProfilController::class, 'index'])->name('profil');
            Route::put('/', [ProfilController::class, 'update'])->name('profil.update');
            Route::post('/password', [ProfilController::class, 'updatePassword'])->name('profil.password');
        });

        Route::get('/email/verify/{id}/{hash}', [ProfilController::class, 'verifyEmail'])->name('verification.verify');
        Route::post('/email/verification-notification', [ProfilController::class, 'resendVerification'])->name('verification.send');

        Route::prefix('ekspor')->group(function () {
            Route::get('/pengumuman', [ExportDataController::class, 'pengumuman'])->name('ekspor.pengumuman');
            Route::get('/surat', [ExportDataController::class, 'surat'])->name('ekspor.surat');
            Route::get('/inventaris', [ExportDataController::class, 'inventaris'])->name('ekspor.inventaris');
            Route::get('/agenda', [ExportDataController::class, 'agenda'])->name('ekspor.agenda');
            Route::get('/jadwal-kegiatan', [ExportDataController::class, 'jadwal'])->name('ekspor.jadwal');
            Route::get('/kepengurusan', [ExportDataController::class, 'kepengurusan'])->name('ekspor.kepengurusan');
            Route::get('/relawan', [ExportDataController::class, 'relawan'])->name('ekspor.relawan');
            Route::get('/jadwal-petugas', [ExportDataController::class, 'jadwalPetugas'])->name('ekspor.jadwal-petugas');
        });
    });
});
