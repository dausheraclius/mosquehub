<?php

namespace Tests\Feature;

use App\Models\Mosque;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    private function buatUser(): User
    {
        Mosque::create(['name' => 'Masjid Test', 'status' => 'aktif']);

        return User::create([
            'name' => 'Andi',
            'email' => 'andi@example.com',
            'password' => 'rahasia123',
            'role' => 'Ketua YMBPK',
            'mosque_id' => 1,
        ]);
    }

    public function test_lupa_password_mengirim_link_reset(): void
    {
        Notification::fake();

        $this->buatUser();

        $this->post('/lupa-password', ['email' => 'andi@example.com'])
            ->assertSessionHas('status');

        Notification::assertSentTo(
            User::where('email', 'andi@example.com')->first(),
            ResetPassword::class
        );
    }

    public function test_reset_password_dengan_token(): void
    {
        $user = $this->buatUser();
        $token = Password::broker()->createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => 'andi@example.com',
            'password' => 'passwordBaru123',
            'password_confirmation' => 'passwordBaru123',
        ])->assertRedirect(route('login'));

        // Password lama tidak lagi berlaku, yang baru bisa login.
        $this->assertFalse(auth()->validate([
            'email' => 'andi@example.com',
            'password' => 'rahasia123',
        ]));
        $this->assertTrue(auth()->validate([
            'email' => 'andi@example.com',
            'password' => 'passwordBaru123',
        ]));
    }
}
