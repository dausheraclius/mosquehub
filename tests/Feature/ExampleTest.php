<?php

namespace Tests\Feature;

use App\Models\Mosque;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    private function masjid(): Mosque
    {
        return Mosque::create(['name' => 'Masjid Test', 'status' => 'aktif']);
    }

    /**
     * Halaman login bisa diakses tanpa autentikasi.
     */
    public function test_halaman_login_dapat_diakses(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /**
     * Root aplikasi mengarahkan tamu ke website publik, bukan ke login.
     */
    public function test_root_mengarahkan_guest_ke_website_publik(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('public.beranda'));
    }

    /**
     * Root aplikasi menampilkan dashboard untuk pengurus yang sudah login.
     */
    public function test_root_menampilkan_dashboard_untuk_user_login(): void
    {
        $this->masjid();
        $user = User::create([
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => 'rahasia123',
            'status' => 'aktif',
            'mosque_id' => 1,
        ]);

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->get('/');

        $response->assertOk();
    }
}
