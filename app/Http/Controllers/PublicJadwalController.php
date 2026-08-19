<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Support\SiteContext;

class PublicJadwalController extends Controller
{
    public function index()
    {
        // Daftar lengkap agenda mendatang (kartu gaya beranda) — bisa dicari & difilter.
        $agendaList = Kegiatan::forMosque(SiteContext::mosqueId())
            ->where('tanggal', '>=', now()->toDateString())
            ->with('relawans')
            ->orderBy('tanggal')
            ->get()
            ->map(fn ($k) => [
                'id' => $k->id,
                'title' => $k->nama,
                'date' => $k->tanggal->translatedFormat('d M Y') . ($k->jam_mulai ? ' - ' . substr($k->jam_mulai, 0, 5) : ''),
                'location' => $k->lokasi ?: '-',
                'kategori' => $k->kategori,
                'desc' => $k->deskripsi ?: '-',
                'pj' => $k->pj,
                'relawan' => $k->relawans->pluck('nama')->values()->toArray(),
            ]);

        // Tampilkan kegiatan 7 hari ke depan (dari hari ini), dikelompokkan per hari.
        $start = now()->startOfDay();
        $end = now()->addDays(6)->endOfDay();

        $kegiatans = Kegiatan::forMosque(SiteContext::mosqueId())
            ->whereBetween('tanggal', [$start, $end])
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $d = now()->addDays($i);
            $days[$d->format('N')] = [
                'hari' => $d->translatedFormat('l'),
                'tanggal' => $d->translatedFormat('d M Y'),
                'items' => [],
            ];
        }

        foreach ($kegiatans as $k) {
            $key = $k->tanggal->format('N');
            if (!isset($days[$key])) {
                continue;
            }

            $loc = $k->lokasi ?: 'Lokasi belum diisi';
            if ($k->pemateri) {
                $loc .= ' - ' . $k->pemateri;
            }

            $days[$key]['items'][] = [
                'time' => $k->jam_mulai ? substr($k->jam_mulai, 0, 5) : '--:--',
                'name' => $k->nama,
                'loc' => $loc,
            ];
        }

        return view('pages.public.jadwal', [
            'jadwal' => array_values($days),
            'agendaList' => $agendaList,
        ]);
    }
}
