<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveUser
{
    /** Pastikan akun yang dinonaktifkan tidak dapat memakai sesi lama. */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->status !== 'nonaktif') {
            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Akun Anda telah dinonaktifkan. Silakan hubungi pengurus aplikasi masjid.',
            ], 403);
        }

        return redirect()->route('login')->withErrors([
            'email' => 'Akun Anda telah dinonaktifkan. Hubungi pengurus aplikasi masjid.',
        ]);
    }
}
