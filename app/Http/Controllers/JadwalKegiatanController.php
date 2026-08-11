<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use Illuminate\Support\Str;

class JadwalKegiatanController extends Controller
{
    private int $mosqueId = 1; // TODO: ganti setelah Auth dibikin

    public function index()
    {
        $today = now()->toDateString();

        $kegiatanHariIni = Kegiatan::where('mosque_id', $this->mosqueId)
            ->where('tanggal', $today)
            ->orderBy('jam_mulai')
            ->get()
            ->map(fn ($k) => $this->toArray($k));

        $semuaHariIni = Kegiatan::where('mosque_id', $this->mosqueId)->where('tanggal', $today)->get();

        $stats = [
            'total' => $semuaHariIni->count(),
            'berlangsung' => $semuaHariIni->where('status', 'Berlangsung')->count(),
            'akanDatang' => $semuaHariIni->where('status', 'Akan Datang')->count(),
            'selesai' => $semuaHariIni->where('status', 'Selesai')->count(),
        ];

        $kegiatanBerikutnya = Kegiatan::where('mosque_id', $this->mosqueId)
            ->where('tanggal', $today)
            ->where('jam_mulai', '>=', now()->format('H:i:s'))
            ->orderBy('jam_mulai')
            ->first();

        $besok = now()->addDay()->toDateString();
        $jadwalBesok = Kegiatan::where('mosque_id', $this->mosqueId)
            ->where('tanggal', $besok)
            ->orderBy('jam_mulai')
            ->take(3)
            ->get();

        $mingguIni = Kegiatan::where('mosque_id', $this->mosqueId)
            ->whereBetween('tanggal', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $riwayatSelesai = $semuaHariIni->where('status', 'Selesai')->sortBy('jam_mulai')->values();

        return view('pages.kegiatan.jadwal', [
            'kegiatanHariIni' => $kegiatanHariIni,
            'stats' => $stats,
            'kegiatanBerikutnya' => $kegiatanBerikutnya,
            'jadwalBesok' => $jadwalBesok,
            'mingguIni' => $mingguIni,
            'riwayatSelesai' => $riwayatSelesai,
        ]);
    }

    private function toArray(Kegiatan $k): array
    {
        return [
            'id' => $k->id,
            'nama' => $k->nama,
            'lokasi' => $k->lokasi,
            'lokasiSlug' => $k->lokasi ? Str::slug($k->lokasi) : '',
            'pemateri' => $k->pemateri ?: $k->pj,
            'jamMulai' => $k->jam_mulai ? substr($k->jam_mulai, 0, 5) : '-',
            'jamSelesai' => $k->jam_selesai ? substr($k->jam_selesai, 0, 5) : '-',
            'jamMulaiDot' => $k->jam_mulai ? str_replace(':', '.', substr($k->jam_mulai, 0, 5)) : '-',
            'kategoriSlug' => Str::slug($k->kategori),
            'kategori' => $k->kategori,
            'statusSlug' => Str::slug($k->status),
            'status' => $k->status,
            'deskripsi' => $k->deskripsi,
            'peserta' => $k->peserta,
        ];
    }
}