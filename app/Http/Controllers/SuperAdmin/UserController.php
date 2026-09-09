<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('mosque')
            ->where('role', '!=', 'Super Admin')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"))
            ->orderByDesc('id')
            ->paginate(30);

        return view('pages.super-admin.users.index', ['users' => $users]);
    }

    public function impersonate(Request $request, User $user)
    {
        session(['impersonator_id' => auth()->id()]);
        Auth::login($user);

        return redirect()->route('dashboard', ['locale' => session('locale', 'id')])->with('success', "Sekarang login sebagai: {$user->name}");
    }

    public function stopImpersonate(Request $request)
    {
        $originalId = session('impersonator_id');
        session()->forget(['impersonator_id', 'active_mosque_id']);

        if ($originalId) {
            Auth::loginUsingId($originalId);
        }

        return redirect()->route('super.mosques', ['locale' => session('locale', 'id')])->with('success', 'Kembali ke akun Super Admin.');
    }
}
