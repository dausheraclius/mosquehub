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
            ->get('/kepengurusan')
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
            ->post('/kepengurusan/jabatan/positions', [
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

    public function test_posisi_menolak_koordinat_tidak_valid(): void
    {
        $this->masjid();
        Jabatan::create(['mosque_id' => 1, 'nama' => 'Ketua YMBPK', 'urutan' => 0]);

        $this->actingAs($this->ketua())
            ->post('/kepengurusan/jabatan/positions', [
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
            ->post('/kepengurusan/jabatan/positions', [
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
            ->post('/kepengurusan/penempatan', [
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
            ->post('/kepengurusan/jabatan/parent', [
                'nama' => 'Sekretaris',
                'parent_nama' => 'Ketua YMBPK',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('jabatans', ['nama' => 'Sekretaris', 'parent_id' => $ketua->id]);
    }
}
