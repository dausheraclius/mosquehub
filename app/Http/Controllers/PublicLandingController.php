<?php

namespace App\Http\Controllers;

use App\Models\Jamaah;
use App\Models\Kegiatan;
use App\Models\PengaturanUmum;
use App\Services\KeuanganStatistik;
use App\Support\SiteContext;

class PublicLandingController extends Controller
{
    public function index()
    {
        $mosqueId = SiteContext::mosqueId();
        $statistik = app(KeuanganStatistik::class);

        $activities = Kegiatan::forMosque($mosqueId)
            ->where('tanggal', '>=', now()->toDateString())
            ->orderBy('tanggal')
            ->take(8)
            ->get()
            ->map(fn ($k) => [
                'id' => $k->id,
                'title' => $k->nama,
                'date' => $k->tanggal->translatedFormat('d F Y').($k->jam_mulai ? ' - '.substr($k->jam_mulai, 0, 5) : ''),
                'location' => $k->lokasi ?: '-',
                'desc' => $k->deskripsi ?: '-',
            ]);

        $pengaturan = PengaturanUmum::forMosque($mosqueId)->first();
        $sholatJadwal = $pengaturan?->sholatJadwal() ?? (new PengaturanUmum)->sholatJadwal();

        // Ringkasan transparansi — sama seperti di dashboard admin.
        $stats = [
            'totalJemaah' => Jamaah::forMosque($mosqueId)->count(),
            'saldoKas' => $statistik->saldoKas($mosqueId),
            'donasiBulanIni' => $statistik->donasiBulanIni($mosqueId),
            'relawanAktif' => $statistik->relawanAktif($mosqueId),
        ];

        return view('pages.public.landing', [
            'activities' => $activities,
            'sholatJadwal' => $sholatJadwal,
            'stats' => $stats,
        ]);
    }
}
