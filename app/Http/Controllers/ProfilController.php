<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return view('pages.profil', [
            'user' => $user,
            'emailVerified' => (bool) $user->email_verified_at,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => 'nullable|string|max:30',
        ]);

        $user->update($validated);

        return response()->json(['success' => true]);
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($validated['old_password'], $user->password)) {
            return response()->json(['message' => 'Password lama salah.'], 422);
        }

        $user->update(['password' => bcrypt($validated['new_password'])]);

        return response()->json(['success' => true]);
    }

    // ============================================================
    // VERIFIKASI EMAIL
    // ============================================================

    public function verifyEmail(Request $request, int $id, string $hash)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Link verifikasi tidak valid atau sudah kedaluwarsa.');
        }

        $user = \App\Models\User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Link verifikasi tidak valid.');
        }

        if ($user->email_verified_at === null) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        return redirect()->route('profil', ['locale' => session('locale', 'id')])->with('status', 'Email berhasil diverifikasi.');
    }

    public function resendVerification(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email Anda sudah terverifikasi.'], 422);
        }

        $user->notify(new VerifyEmail());

        return response()->json(['success' => true]);
    }
}
