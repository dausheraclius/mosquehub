<?php

namespace Tests\Feature;

use App\Models\Mosque;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
    }
}
