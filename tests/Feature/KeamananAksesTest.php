<?php

namespace Tests\Feature;

use App\Models\Mosque;
use App\Models\KasTransaction;
use App\Models\Jamaah;
use App\Models\PengaturanUmum;
use App\Models\Pengumuman;
use App\Models\User;
use App\Services\MosqueBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KeamananAksesTest extends TestCase
{
    use RefreshDatabase;

    private function masjid(): Mosque
    {
        return Mosque::create(['name' => 'Masjid Test', 'status' => 'aktif']);
    }

    public function test_user_nonaktif_tidak_bisa_login(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $this->masjid();
        $user = User::create([
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => 'rahasia123',
            'status' => 'nonaktif',
            'mosque_id' => 1,
        ]);

        $response = $this->post('/login', [
            'email' => 'budi@example.com',
            'password' => 'rahasia123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_role_ketua_bisa_akses_semua_menu(): void
    {
        $this->masjid();
        $ketua = User::create([
            'name' => 'Ketua',
            'email' => 'ketua@example.com',
            'password' => 'rahasia123',
            'role' => 'Ketua YMBPK',
            'mosque_id' => 1,
        ]);

        $this->actingAs($ketua)
            ->get('/data-jamaah')
            ->assertOk();
    }

    public function test_user_tanpa_izin_dapat_403(): void
    {
        $this->masjid();
        $user = User::create([
            'name' => 'Petugas',
            'email' => 'petugas@example.com',
            'password' => 'rahasia123',
            'role' => 'Petugas Zakat',
            'permissions' => ['Infaq / Sodaqoh (Zakat)'],
            'mosque_id' => 1,
        ]);

        // Tidak punya izin data jamaah → ditolak
        $this->actingAs($user)
            ->get('/data-jamaah')
            ->assertForbidden();

        // Punya izin ziswaf → boleh
        $this->actingAs($user)
            ->get('/keuangan/infaq-sodaqoh')
            ->assertOk();

        // Dashboard selalu boleh
        $this->actingAs($user)
            ->get('/')
            ->assertOk();
    }

    public function test_ekspor_modul_ditolak_bila_tidak_memiliki_izin_modulnya(): void
    {
        $this->masjid();
        $user = User::create([
            'name' => 'Petugas',
            'email' => 'petugas-ekspor@example.com',
            'password' => 'rahasia123',
            'role' => 'Petugas Zakat',
            'permissions' => ['Infaq / Sodaqoh (Zakat)'],
            'mosque_id' => 1,
        ]);

        $this->actingAs($user)
            ->get('/ekspor/pengumuman')
            ->assertForbidden();
    }

    public function test_log_aktivitas_hanya_dapat_diakses_ketua(): void
    {
        $this->masjid();
        $petugas = User::create([
            'name' => 'Petugas',
            'email' => 'petugas-log@example.com',
            'password' => 'rahasia123',
            'role' => 'Petugas Zakat',
            'mosque_id' => 1,
        ]);
        $ketua = User::create([
            'name' => 'Ketua',
            'email' => 'ketua-log@example.com',
            'password' => 'rahasia123',
            'role' => 'Ketua YMBPK',
            'mosque_id' => 1,
        ]);

        $this->actingAs($petugas)->get('/pengawasan/log-aktivitas')->assertForbidden();
        $this->actingAs($ketua)->get('/pengawasan/log-aktivitas')->assertOk();
    }

    public function test_ketua_dapat_menonaktifkan_user_biasa_meski_hanya_ada_satu_ketua(): void
    {
        $this->masjid();
        $ketua = User::create([
            'name' => 'Ketua', 'email' => 'ketua-status@example.com', 'password' => 'rahasia123',
            'role' => 'Ketua YMBPK', 'mosque_id' => 1,
        ]);
        $petugas = User::create([
            'name' => 'Petugas', 'email' => 'petugas-status@example.com', 'password' => 'rahasia123',
            'role' => 'Petugas Zakat', 'status' => 'aktif', 'mosque_id' => 1,
        ]);

        $this->actingAs($ketua)
            ->patch('/pengaturan/user-management/' . $petugas->id . '/status', ['status' => 'nonaktif'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', ['id' => $petugas->id, 'status' => 'nonaktif']);
    }

    public function test_sesi_user_nonaktif_dicabut_pada_request_berikutnya(): void
    {
        $this->masjid();
        $user = User::create([
            'name' => 'Nonaktif', 'email' => 'nonaktif-sesi@example.com', 'password' => 'rahasia123',
            'status' => 'nonaktif', 'permissions' => ['data-jamaah'], 'mosque_id' => 1,
        ]);

        $this->actingAs($user)->get('/data-jamaah')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_ketua_dapat_menghapus_transaksi_kas(): void
    {
        $this->masjid();
        $ketua = User::create([
            'name' => 'Ketua', 'email' => 'ketua-kas@example.com', 'password' => 'rahasia123',
            'role' => 'Ketua YMBPK', 'mosque_id' => 1,
        ]);
        $transaksi = KasTransaction::create([
            'mosque_id' => 1, 'tanggal' => now()->toDateString(), 'jenis' => 'Infaq',
            'pemasukan' => 100000, 'pengeluaran' => 0,
        ]);

        $this->actingAs($ketua)
            ->delete('/keuangan/kas-masjid/' . $transaksi->id)
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('kas_transactions', ['id' => $transaksi->id]);
    }

    public function test_pengumuman_dihapus_dengan_respons_json(): void
    {
        $this->masjid();
        $ketua = User::create([
            'name' => 'Ketua', 'email' => 'ketua-pengumuman@example.com', 'password' => 'rahasia123',
            'role' => 'Ketua YMBPK', 'mosque_id' => 1,
        ]);
        $pengumuman = Pengumuman::create([
            'mosque_id' => 1,
            'judul' => 'Kerja bakti',
            'isi' => 'Dilaksanakan hari Minggu.',
            'status' => 'Aktif',
            'tanggal' => now()->toDateString(),
        ]);

        $this->actingAs($ketua)
            ->deleteJson('/pengumuman/' . $pengumuman->id)
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('pengumumans', ['id' => $pengumuman->id]);
    }

    public function test_backup_dapat_dipulihkan_ke_masjid_yang_sama(): void
    {
        $this->masjid();
        $jamaah = Jamaah::create([
            'mosque_id' => 1, 'nama' => 'Ahmad', 'jenis_kelamin' => 'Laki-laki', 'no_hp' => '0812',
            'status_jamaah' => 'Aktif', 'tanggal_bergabung' => now()->toDateString(),
        ]);
        $service = app(MosqueBackupService::class);
        $backup = json_encode($service->snapshot(1));

        $jamaah->delete();
        $service->restore($backup, 1);

        $this->assertDatabaseHas('jamaah', ['mosque_id' => 1, 'nama' => 'Ahmad']);
    }

    public function test_perintah_backup_otomatis_menyimpan_file_backup(): void
    {
        Storage::fake('local');
        $this->masjid();
        PengaturanUmum::create([
            'mosque_id' => 1, 'backup_otomatis' => true, 'backup_frekuensi' => 'Setiap Hari',
        ]);

        $this->artisan('mosquehub:backup')->assertSuccessful();

        $this->assertNotEmpty(Storage::disk('local')->allFiles('backups/masjid-1'));
    }

    public function test_halaman_profil_dapat_diakses_dan_mengubah_data(): void
    {
        $this->masjid();
        $user = User::create([
            'name' => 'Siti',
            'email' => 'siti@example.com',
            'password' => 'rahasia123',
            'role' => 'Sekretaris',
            'permissions' => ['pengumuman'],
            'mosque_id' => 1,
        ]);

        $this->actingAs($user)
            ->get('/profil')
            ->assertOk();

        $this->actingAs($user)
            ->put('/profil', ['name' => 'Siti Aminah', 'email' => 'siti@example.com', 'phone' => '0812'])
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Siti Aminah']);
        $this->assertDatabaseHas('activity_log', [
            'description' => 'Memperbarui data (profil.update)',
            'causer_id' => $user->id,
        ]);
    }
}
