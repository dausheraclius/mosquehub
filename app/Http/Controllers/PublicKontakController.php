<?php

namespace App\Http\Controllers;

use App\Models\KontakPesan;
use App\Support\SiteContext;
use Illuminate\Http\Request;

class PublicKontakController extends Controller
{
    public function index()
    {
        return view('pages.public.kontak');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'subjek' => ['nullable', 'string', 'max:150'],
            'pesan' => ['required', 'string', 'max:2000'],
        ]);

        KontakPesan::create($validated + [
            'mosque_id' => SiteContext::mosqueId(),
            'status' => 'baru',
        ]);

        return redirect()
            ->route('public.kontak')
            ->with('success', 'Pesan berhasil terkirim. Terima kasih, kami akan segera menghubungi Anda.');
    }
}