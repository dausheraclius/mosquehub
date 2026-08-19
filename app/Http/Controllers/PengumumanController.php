<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengumumanRequest;
use App\Models\Pengumuman;
use App\Support\Concerns\HasMosqueContext;

class PengumumanController extends Controller
{
    use HasMosqueContext;

    public function index()
    {
        $list = Pengumuman::forMosque($this->mosqueId)->orderByDesc('tanggal')->get();

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

    public function store(PengumumanRequest $request)
    {
        $validated = $request->validated();
        $validated['mosque_id'] = $this->mosqueId;

        Pengumuman::create($validated);

        return redirect()->route('pengumuman')->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function update(PengumumanRequest $request, Pengumuman $pengumuman)
    {
        $pengumuman->update($request->validated());

        return redirect()->route('pengumuman')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman)
    {
        $pengumuman->delete();

        // Endpoint ini dipanggil lewat fetch dari halaman pengumuman. Mengirim
        // JSON mencegah fetch mengikuti redirect dan memuat ulang halaman.
        return response()->json(['success' => true]);
    }
}
