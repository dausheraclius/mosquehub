<?php

namespace Tests\Feature;

use App\Models\Jabatan;
use App\Models\Jamaah;
use App\Models\Mosque;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KepengurusanTest extends TestCase
{
    use RefreshDatabase;

    private function masjid(): Mosque
    {
        return Mosque::create(['name' => 'Masjid Test', 'status' => 'aktif']);
    }

    private function ketua(): User
    {
        return User::create([
            'name' => 'Ketua',
            'email' => 'ketua@example.com',
            'password' => 'rahasia123',
            'role' => 'Ketua YMBPK',
            'mosque_id' => 1,
        ]);
    }

    public function test_halaman_kepengurusan_mengirim_posisi_org_ke_frontend(): void
    {
        $this->masjid();
        Jabatan::create(['mosque_id' => 1, 'nama' => 'Ketua YMBPK', 'urutan' => 0, 'posisi_x' => 500, 'posisi_y' => 50]);

        $this->actingAs($this->ketua())
            ->get('/id/kepengurusan')
            ->assertOk()
            ->assertSee('__POSISI_ORG__')
            ->assertSee('500');
    }

    public function test_posisi_jabatan_dapat_disimpan(): void
    {
        $this->masjid();
        Jabatan::create(['mosque_id' => 1, 'nama' => 'Ketua YMBPK', 'urutan' => 0]);
        Jabatan::create(['mosque_id' => 1, 'nama' => 'Sekretaris', 'urutan' => 1, 'parent_id' => 1]);

        $this->actingAs($this->ketua())
            ->post('/id/kepengurusan/jabatan/positions', [
                'positions' => [
                    'Ketua YMBPK' => ['x' => 500, 'y' => 50],
                    'Sekretaris' => ['x' => 250, 'y' => 200],
                ],
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('jabatans', ['nama' => 'Ketua YMBPK', 'posisi_x' => 500, 'posisi_y' => 50]);
        $this->assertDatabaseHas('jabatans', ['nama' => 'Sekretaris', 'posisi_x' => 250, 'posisi_y' => 200]);
    }

    public function test_posisi_ikram_disimpan_terpisah_dari_ymbpk(): void
    {
        $this->masjid();
        $ymbpk = Jabatan::create([
            'mosque_id' => 1,
            'organisasi' => 'YMBPK',
            'nama' => 'Ketua',
            'urutan' => 0,
            'posisi_x' => 100,
            'posisi_y' => 100,
        ]);
        $ikram = Jabatan::create([
            'mosque_id' => 1,
            'organisasi' => 'IKRAM',
            'nama' => 'Ketua',
            'urutan' => 0,
        ]);

        $this->actingAs($this->ketua())
            ->post('/id/kepengurusan/jabatan/positions', [
                'organisasi' => 'IKRAM',
                'positions' => ['Ketua' => ['x' => 420, 'y' => 180]],
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('jabatans', ['id' => $ikram->id, 'posisi_x' => 420, 'posisi_y' => 180]);
        $this->assertDatabaseHas('jabatans', ['id' => $ymbpk->id, 'posisi_x' => 100, 'posisi_y' => 100]);
    }

    public function test_posisi_menolak_koordinat_tidak_valid(): void
    {
        $this->masjid();
        Jabatan::create(['mosque_id' => 1, 'nama' => 'Ketua YMBPK', 'urutan' => 0]);

        $this->actingAs($this->ketua())
            ->post('/id/kepengurusan/jabatan/positions', [
                'positions' => [
                    'Ketua YMBPK' => ['x' => -5, 'y' => 'bukan-angka'],
                ],
            ])
            ->assertSessionHasErrors(['positions.Ketua YMBPK.x', 'positions.Ketua YMBPK.y']);

        $this->assertDatabaseHas('jabatans', ['nama' => 'Ketua YMBPK', 'posisi_x' => null, 'posisi_y' => null]);
    }

    public function test_posisi_mengabaikan_nama_jabatan_yang_tidak_ada(): void
    {
        $this->masjid();
        Jabatan::create(['mosque_id' => 1, 'nama' => 'Ketua YMBPK', 'urutan' => 0]);

        $this->actingAs($this->ketua())
            ->post('/id/kepengurusan/jabatan/positions', [
                'positions' => [
                    'Jabatan Hantu' => ['x' => 10, 'y' => 10],
                ],
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('jabatans', ['nama' => 'Jabatan Hantu']);
    }

    public function test_penempatan_jamaah_tetap_berfungsi(): void
    {
        $this->masjid();
        $jamaah = Jamaah::create([
            'mosque_id' => 1, 'nama' => 'Ahmad', 'jenis_kelamin' => 'Laki-laki', 'no_hp' => '0812',
            'status_jamaah' => 'Aktif', 'tanggal_bergabung' => now()->toDateString(),
        ]);
        Jabatan::create(['mosque_id' => 1, 'nama' => 'Ketua YMBPK', 'urutan' => 0]);

        $this->actingAs($this->ketua())
            ->post('/id/kepengurusan/penempatan', [
                'penempatan' => ['Ketua YMBPK' => $jamaah->id],
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('jabatans', ['nama' => 'Ketua YMBPK', 'jamaah_id' => $jamaah->id]);
    }

    public function test_ubah_parent_jabatan_tetap_berfungsi(): void
    {
        $this->masjid();
        $ketua = Jabatan::create(['mosque_id' => 1, 'nama' => 'Ketua YMBPK', 'urutan' => 0]);
        Jabatan::create(['mosque_id' => 1, 'nama' => 'Sekretaris', 'urutan' => 1]);

        $this->actingAs($this->ketua())
            ->post('/id/kepengurusan/jabatan/parent', [
                'nama' => 'Sekretaris',
                'parent_nama' => 'Ketua YMBPK',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('jabatans', ['nama' => 'Sekretaris', 'parent_id' => $ketua->id]);
    }

    public function test_jabatan_dapat_memiliki_nama_yang_sama_di_organisasi_berbeda(): void
    {
        $this->masjid();
        Jabatan::create(['mosque_id' => 1, 'organisasi' => 'YMBPK', 'nama' => 'Ketua', 'urutan' => 0]);

        $this->actingAs($this->ketua())
            ->post('/id/kepengurusan/jabatan', [
                'organisasi' => 'IKRAM',
                'nama' => 'Ketua',
            ])
            ->assertOk();

        $this->assertDatabaseHas('jabatans', [
            'mosque_id' => 1,
            'organisasi' => 'IKRAM',
            'nama' => 'Ketua',
        ]);
    }

    public function test_parent_menolak_struktur_siklik(): void
    {
        $this->masjid();
        $ketua = Jabatan::create(['mosque_id' => 1, 'nama' => 'Ketua', 'urutan' => 0]);
        $sekretaris = Jabatan::create(['mosque_id' => 1, 'nama' => 'Sekretaris', 'parent_id' => $ketua->id, 'urutan' => 1]);

        $this->actingAs($this->ketua())
            ->post('/id/kepengurusan/jabatan/parent', [
                'nama' => 'Ketua',
                'parent_nama' => 'Sekretaris',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Struktur jabatan tidak boleh membentuk siklus.');

        $this->assertDatabaseHas('jabatans', ['id' => $ketua->id, 'parent_id' => null]);
        $this->assertDatabaseHas('jabatans', ['id' => $sekretaris->id, 'parent_id' => $ketua->id]);
    }

    public function test_penempatan_dapat_dikosongkan_dan_tidak_boleh_lintas_organisasi(): void
    {
        $this->masjid();
        $jamaah = Jamaah::create([
            'mosque_id' => 1, 'nama' => 'Ahmad', 'jenis_kelamin' => 'Laki-laki', 'no_hp' => '0812',
            'status_jamaah' => 'Aktif', 'tanggal_bergabung' => now()->toDateString(),
        ]);
        $ymbpk = Jabatan::create(['mosque_id' => 1, 'nama' => 'Ketua', 'jamaah_id' => $jamaah->id, 'urutan' => 0]);
        Jabatan::create(['mosque_id' => 1, 'organisasi' => 'IKRAM', 'nama' => 'Ketua', 'urutan' => 0]);

        $this->actingAs($this->ketua())
            ->post('/id/kepengurusan/penempatan', [
                'organisasi' => 'YMBPK',
                'penempatan' => ['Ketua' => null],
            ])
            ->assertOk();
        $this->assertDatabaseHas('jabatans', ['id' => $ymbpk->id, 'jamaah_id' => null]);

        $this->post('/id/kepengurusan/penempatan', [
            'organisasi' => 'IKRAM',
            'penempatan' => ['Ketua' => $jamaah->id],
        ])->assertOk();

        $this->post('/id/kepengurusan/penempatan', [
            'organisasi' => 'YMBPK',
            'penempatan' => ['Ketua' => $jamaah->id],
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Jemaah tersebut sudah ditempatkan di organisasi lain.');
    }
}
