<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    private int $mosqueId = 1; // TODO: ganti setelah Auth dibikin

    public function index()
    {
        $list = Pengumuman::where('mosque_id', $this->mosqueId)->orderByDesc('tanggal')->get();

        $stats = [
            'total' => $list->count(),
            'aktif' => $list->where('status', 'Aktif')->count(),
            'terjadwal' => $list->where('status', 'Terjadwal')->count(),
            'arsip' => $list->where('status', 'Arsip')->count(),
        ];

        $featured = $list->firstWhere('status', 'Aktif') ?? $list->first();

        $pengumumanList = $list->map(fn ($p) => [
            'id' => $p->id,
            'judul' => $p->judul,
            'isi' => $p->isi,
            'kategori' => $p->kategori,
            'status' => $p->status,
            'tanggal' => $p->tanggal->format('d M Y'),
            'tanggalRaw' => $p->tanggal->format('Y-m-d'),
        ])->values();

        return view('pages.pengumuman', [
            'list' => $list,
            'stats' => $stats,
            'featured' => $featured,
            'pengumumanList' => $pengumumanList,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'kategori' => 'nullable|string|max:100',
            'status' => 'required|in:Aktif,Terjadwal,Arsip',
            'tanggal' => 'required|date',
        ]);
        $validated['mosque_id'] = $this->mosqueId;

        Pengumuman::create($validated);

        return redirect()->route('pengumuman')->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function update(Request $request, Pengumuman $pengumuman)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'kategori' => 'nullable|string|max:100',
            'status' => 'required|in:Aktif,Terjadwal,Arsip',
            'tanggal' => 'required|date',
        ]);

        $pengumuman->update($validated);

        return redirect()->route('pengumuman')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman)
    {
        $pengumuman->delete();

        return redirect()->route('pengumuman')->with('success', 'Pengumuman dihapus.');
    }
}