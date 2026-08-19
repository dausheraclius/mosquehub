<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Mosque;
use App\Models\User;
use App\Support\SiteContext;
use Illuminate\Http\Request;

class MosqueController extends Controller
{
    public function index()
    {
        $mosques = Mosque::withCount('users')->orderByDesc('id')->get();

        return view('pages.super-admin.mosques.index', ['mosques' => $mosques]);
    }

    public function show(Mosque $mosque)
    {
        $users = User::where('mosque_id', $mosque->id)->orderBy('name')->get();

        return view('pages.super-admin.mosques.show', ['mosque' => $mosque, 'users' => $users]);
    }

    public function updateStatus(Request $request, Mosque $mosque)
    {
        $validated = $request->validate(['status' => 'required|in:aktif,nonaktif']);
        $mosque->update(['status' => $validated['status']]);

        return back()->with('success', 'Status masjid diperbarui.');
    }

    public function switchTo(Request $request, Mosque $mosque)
    {
        SiteContext::setActiveMosque($mosque->id);

        return redirect()->route('dashboard', ['locale' => $request->route('locale')])->with('success', "Sekarang lo lagi lihat sebagai: {$mosque->name}");
    }
}
